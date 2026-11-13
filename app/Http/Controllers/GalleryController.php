<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    /**
     * Discover homepage.
     * Uses real, approved database content only.
     */
    public function home(): View
    {
        return view('artfy.home', [
            'artworks' => array_slice(
                $this->publicArtworkCollection(),
                0,
                3
            ),
            'artists' => $this->artistCollection(),
            'exhibitions' => $this->exhibitionCollection(),
            'exhibitionAd' => $this->discoverExhibitionAd(),
        ]);
    }

    /**
     * Explore artworks with search, format, medium, price and sorting.
     */
    public function artworks(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $artworkFormat = strtolower(trim(
            (string) $request->query('artwork_type', '')
        ));

        if (! in_array($artworkFormat, ['', 'physical', 'digital'], true)) {
            $artworkFormat = '';
        }

        // Support older links that use ?art_type=...
        $mediumFilter = trim((string) $request->query(
            'medium',
            $request->query('art_type', '')
        ));

        $maxPriceInput = $request->query('max_price', '');
        $sort = (string) $request->query('sort', 'latest');

        $maxPrice = is_numeric($maxPriceInput)
            && (float) $maxPriceInput >= 0
                ? (float) $maxPriceInput
                : null;

        $allowedSorts = [
            'latest',
            'oldest',
            'popular',
            'price-low',
            'price-high',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        $discoveryArtworks = $this->publicArtworkCollection();
        $highestLikeCount = $discoveryArtworks
            ? max(array_map(fn (array $artwork): int => (int) ($artwork['likes'] ?? 0), $discoveryArtworks))
            : 0;
        $topArtworks = $highestLikeCount > 0
            ? array_values(array_filter(
                $discoveryArtworks,
                fn (array $artwork): bool => (int) ($artwork['likes'] ?? 0) === $highestLikeCount
            ))
            : [];
        $recentSince = now()->subDays(3)->timestamp;
        $newArtworks = array_slice(array_values(array_filter(
            $discoveryArtworks,
            fn (array $artwork): bool => ($artwork['_created_at'] ?? 0) >= $recentSince
        )), 0, 6);
        $showDiscoverySections = $query === ''
            && $artworkFormat === ''
            && $mediumFilter === ''
            && $maxPrice === null
            && $sort === 'latest';

        $artworks = $discoveryArtworks;

        $artworks = array_values(array_filter(
            $artworks,
            function (array $artwork) use (
                $query,
                $artworkFormat,
                $mediumFilter,
                $maxPrice
            ): bool {
                $searchText = implode(' ', [
                    $artwork['title'] ?? '',
                    $artwork['artist'] ?? '',
                    $artwork['category'] ?? '',
                    $artwork['type'] ?? '',
                    $artwork['description'] ?? '',
                ]);

                $matchesQuery = $query === ''
                    || str_contains(
                        mb_strtolower($searchText),
                        mb_strtolower($query)
                    );

                $format = strtolower(trim(
                    (string) ($artwork['artwork_type'] ?? 'physical')
                ));

                $matchesFormat = $artworkFormat === ''
                    || $format === $artworkFormat;

                $matchesMedium = $mediumFilter === ''
                    || $this->matchesArtType($artwork, $mediumFilter);

                $price = $artwork['price'] ?? null;

                $matchesPrice = $maxPrice === null
                    || (
                        $price !== null
                        && (float) $price <= $maxPrice
                    );

                return $matchesQuery
                    && $matchesFormat
                    && $matchesMedium
                    && $matchesPrice;
            }
        ));

        switch ($sort) {
            case 'oldest':
                usort(
                    $artworks,
                    fn (array $a, array $b): int => ($a['_created_at'] ?? 0)
                        <=> ($b['_created_at'] ?? 0)
                );
                break;

            case 'popular':
                usort(
                    $artworks,
                    fn (array $a, array $b): int => ($b['likes'] ?? 0)
                        <=> ($a['likes'] ?? 0)
                );
                break;

            case 'price-low':
                usort(
                    $artworks,
                    fn (array $a, array $b): int => ($a['price'] ?? PHP_INT_MAX)
                        <=> ($b['price'] ?? PHP_INT_MAX)
                );
                break;

            case 'price-high':
                usort(
                    $artworks,
                    fn (array $a, array $b): int => ($b['price'] ?? -1)
                        <=> ($a['price'] ?? -1)
                );
                break;

            case 'latest':
            default:
                usort(
                    $artworks,
                    fn (array $a, array $b): int => ($b['_created_at'] ?? 0)
                        <=> ($a['_created_at'] ?? 0)
                );
                break;
        }

        foreach ($artworks as &$artwork) {
            unset($artwork['_created_at']);
        }

        unset($artwork);

        return view('artfy.listing', [
            'title' => 'Explore artworks',
            'eyebrow' => 'The Artfy collection',
            'description' => 'Discover original work from independent artists, all in one place.',
            'pageType' => 'artworks',
            'items' => $artworks,
            'query' => $query,
            'artworkFormat' => $artworkFormat,
            'mediumFilter' => $mediumFilter,
            'artType' => $mediumFilter,
            'maxPrice' => $maxPriceInput,
            'sort' => $sort,
            'newArtworks' => $newArtworks,
            'topArtworks' => $topArtworks,
            'showDiscoverySections' => $showDiscoverySections,
        ]);
    }

    /**
     * Show the signed-in art lover's saved artworks.
     */
    public function favorites(): View
    {
        $artworks = array_values(array_filter(
            $this->publicArtworkCollection(includeSold: true),
            fn (array $artwork): bool => $artwork['is_saved']
        ));

        return view('artfy.favorites', [
            'items' => $artworks,
        ]);
    }

    /**
     * Match the selected artistic medium against the saved category
     * and medium. Format filtering is handled separately.
     */
    private function matchesArtType(
        array $artwork,
        string $selectedType
    ): bool {
        $normalize = static function (string $value): string {
            $value = mb_strtolower(trim($value));

            return trim(
                preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? ''
            );
        };

        $category = $normalize(
            (string) ($artwork['category'] ?? '')
        );

        $medium = $normalize(
            (string) ($artwork['type'] ?? '')
        );

        $selected = $normalize($selectedType);

        $values = array_unique(array_filter([
            $category,
            $medium,
        ]));

        $aliases = [
            'oil painting' => [
                'oil painting', 'oil', 'oil on canvas',
                'oil on linen', 'oil on wood', 'oil paint', 'oil paints',
            ],
            'watercolor painting' => [
                'watercolor painting', 'watercolour painting',
                'watercolor', 'watercolour', 'watercolor on paper',
                'watercolour on paper', 'watercolor paint',
                'watercolour paint',
            ],
            'pastel painting' => [
                'pastel painting', 'pastel', 'soft pastel',
                'oil pastel', 'pastels', 'pastel on paper',
            ],
            'acrylic painting' => [
                'acrylic painting', 'acrylic', 'acrylic paint',
                'acrylic on canvas', 'acrylic on paper',
            ],
            'ink wash painting' => [
                'ink wash painting', 'ink wash', 'ink wash art',
                'literati painting', 'chinese ink painting',
                'sumi e', 'sumi e painting',
            ],
            'encaustic painting' => [
                'encaustic painting', 'encaustic', 'hot wax painting',
                'hot wax', 'wax painting',
            ],
            'spray painting' => [
                'spray painting', 'spray paint', 'spray art',
                'aerosol art',
            ],
            'sketching' => [
                'sketching', 'sketch', 'pencil sketch', 'pen sketch',
                'charcoal sketch', 'sketch drawing', 'drawing',
            ],
            'photography' => [
                'photography', 'photograph', 'photo',
                'digital photography', 'film photography',
                'photographic print',
            ],
            'digital painting' => [
                'digital painting', 'digital art', 'digital illustration',
                'digital drawing', 'digital',
            ],
        ];

        $selectedAliases = $aliases[$selected] ?? [$selected];

        foreach ($values as $value) {
            if (in_array($value, $selectedAliases, true)) {
                return true;
            }

            foreach ($selectedAliases as $alias) {
                if ($alias !== '' && str_contains($value, $alias)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * List artists with approved applications and verified payments.
     */
    public function artists(): View
    {
        $artists = $this->artistCollection();
        $highestFollowerCount = $artists
            ? max(array_map(fn (array $artist): int => (int) ($artist['follower_count'] ?? 0), $artists))
            : 0;
        $topArtists = $highestFollowerCount > 0
            ? array_values(array_filter(
                $artists,
                fn (array $artist): bool => (int) ($artist['follower_count'] ?? 0) === $highestFollowerCount
            ))
            : [];
        $recentSince = now()->subDays(3)->timestamp;
        $newArtists = array_values(array_filter(
            $artists,
            fn (array $artist): bool => ($artist['_created_at'] ?? 0) >= $recentSince
        ));
        usort($newArtists, fn (array $a, array $b): int => ($b['_created_at'] ?? 0)
            <=> ($a['_created_at'] ?? 0));

        return view('artfy.listing', [
            'title' => 'Meet the artists',
            'eyebrow' => 'Creative minds',
            'description' => 'Get to know the people behind the art and explore their growing portfolios.',
            'pageType' => 'artists',
            'items' => $artists,
            'topArtists' => $topArtists,
            'newArtists' => array_slice($newArtists, 0, 6),
        ]);
    }

    /**
     * Display exhibitions.
     */
    public function exhibitions(): View
    {
        return view('artfy.listing', [
            'title' => 'Online exhibitions',
            'eyebrow' => 'Curated for you',
            'description' => 'Step into collections of artwork brought together around a shared idea.',
            'pageType' => 'exhibitions',
            'items' => $this->exhibitionCollection(),
        ]);
    }

    /**
     * Display an approved artwork.
     *
     * Only the owning artist can manage their uploaded artwork.
     */
    public function artwork(string $slug): View
    {
        if (! preg_match('/^uploaded-(\d+)$/', $slug, $matches)) {
            abort(404);
        }

        $uploadedArtwork = Artwork::with('artist.artistProfile')
            ->withCount('likes')
            ->whereIn('status', ['available', 'sold'])
            ->where('moderation_status', 'approved')
            ->find((int) $matches[1]);

        abort_unless($uploadedArtwork, 404);

        $artist = $uploadedArtwork->artist;
        $profile = $artist?->artistProfile;
        $canManage = auth()->check()
            && (int) auth()->id() === (int) $uploadedArtwork->user_id
            && auth()->user()->isArtist();
        $viewerId = auth()->id();
        $comments = $uploadedArtwork->comments()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => fn ($query) => $query
                    ->with('user')
                    ->withCount('likes')
                    ->withExists([
                        'likes as liked_by_viewer' => fn ($likes) => $likes
                            ->where('user_id', $viewerId),
                    ])
                    ->oldest(),
            ])
            ->withCount('likes')
            ->withExists([
                'likes as liked_by_viewer' => fn ($query) => $query
                    ->where('user_id', $viewerId),
            ])
            ->latest()
            ->get();
        $likeUsers = $canManage
            ? $uploadedArtwork->likes()
                ->select('users.id', 'users.name')
                ->latest('artwork_likes.created_at')
                ->get()
            : collect();

        $artwork = [
            'id' => $uploadedArtwork->id,
            'slug' => 'uploaded-'.$uploadedArtwork->id,
            'title' => $uploadedArtwork->title,
            'description' => $uploadedArtwork->description
                ?: 'Explore this original artwork on Artfy.',
            'artist' => $profile?->artist_name
                ?: ($artist?->name ?? 'Artfy Artist'),
            'artist_slug' => $artist
                ? 'artist-'.$artist->id
                : null,
            'category' => $uploadedArtwork->category ?: 'Other',
            'type' => $uploadedArtwork->medium ?: 'Not specified',
            'price' => $uploadedArtwork->price,
            'likes' => $uploadedArtwork->likes_count,
            'color' => 'green',
            'image' => $uploadedArtwork->image,
            'is_uploaded' => true,
            'status' => $uploadedArtwork->status,
            'is_sold' => $uploadedArtwork->status === 'sold',
            'can_manage' => $canManage,
            'is_liked' => auth()->check()
                && auth()->user()->isArtLover()
                && auth()->user()->likedArtworks()
                    ->whereKey($uploadedArtwork->id)
                    ->exists(),
            'is_saved' => auth()->check()
                && auth()->user()->isArtLover()
                && auth()->user()->savedArtworks()
                    ->whereKey($uploadedArtwork->id)
                    ->exists(),
        ];

        return view('artfy.detail', [
            'title' => $artwork['title'],
            'eyebrow' => 'Original artwork',
            'description' => $artwork['description'],
            'item' => $artwork,
            'pageType' => 'artwork',
            'comments' => $comments,
            'likeUsers' => $likeUsers,
        ]);
    }

    /**
     * Display a public artist profile.
     *
     * This is the shared visual design, but public visitors must not
     * receive artist-management buttons.
     */
    public function artist(string $slug): View
    {
        if (! preg_match('/^artist-(\d+)$/', $slug, $matches)) {
            abort(404);
        }

        $artist = User::with([
            'artistProfile',
            'artistApplication',
        ])
            ->where('role', 'artist')
            ->whereHas('artistApplication', function ($query) {
                $query->where('status', 'approved')
                    ->where('payment_status', 'verified');
            })
            ->findOrFail((int) $matches[1]);

        $artworks = Artwork::where('user_id', $artist->id)
            ->withCount('likes')
            ->whereIn('status', ['available', 'sold'])
            ->where('moderation_status', 'approved')
            ->latest()
            ->get();

        return view('artfy.artist-profile-show', [
            'user' => $artist,
            'profile' => $artist->artistProfile,
            'artworks' => $artworks,
            'followerCount' => $artist->followers()->count(),
            'isFollowingArtist' => auth()->user()?->following()
                ->whereKey($artist->id)
                ->exists() ?? false,
            'adminPreview' => false,
            'canManageArtistProfile' => false,
        ]);
    }

    /**
     * Exhibition details.
     */
    public function exhibition(string $slug): View
    {
        app(\App\Services\ExhibitionAwardService::class)
            ->settleAllClosedExhibitions();

        $exhibition = Exhibition::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $visitOpen = $exhibition->visitIsOpen();
        $adminPreview = auth()->check()
            && auth()->user()->role === 'admin';
        $galleryOpen = $exhibition->visitHasStarted() || $adminPreview;
        $resultsVisible = $exhibition->visitHasEnded();

        if ($galleryOpen) {
            $exhibition->load([
                'applications' => fn ($query) => $query
                    ->where('status', 'confirmed')
                    ->with(['artist.artistProfile'])
                    ->withCount('votes')
                    ->withSum('scores', 'score')
                    ->withExists([
                        'votes as voted_by_viewer' => fn ($votes) => $votes
                            ->where('user_id', auth()->id() ?? 0),
                    ])
                    ->withCount([
                        'orders as verified_sales_count' => fn ($orders) => $orders
                            ->where('payment_status', 'verified'),
                        'orders as pending_sales_count' => fn ($orders) => $orders
                            ->where('payment_status', 'pending')
                            ->where('order_status', 'pending'),
                    ]),
            ]);

            $exhibition->applications->each(function ($application): void {
                $application->sold = (int) $application->verified_sales_count > 0;
                $application->purchase_pending = ! $application->sold
                    && (int) $application->pending_sales_count > 0;
            });
        }

        $applications = $galleryOpen
            ? $exhibition->applications
            : collect();

        if ($resultsVisible) {
            $applications = $applications
                ->map(function ($application): object {
                    $application->total_points = (int) $application->votes_count
                        + (int) ($application->scores_sum_score ?? 0);

                    return $application;
                })
                ->sort(function ($left, $right): int {
                    $pointsOrder = $right->total_points <=> $left->total_points;

                    return $pointsOrder !== 0
                        ? $pointsOrder
                        : $left->id <=> $right->id;
                })
                ->values();

            $rank = 0;
            $previousPoints = null;
            foreach ($applications as $index => $application) {
                if ($previousPoints !== $application->total_points) {
                    $rank = $index + 1;
                    $previousPoints = $application->total_points;
                }
                $application->rank = $rank;
            }

            $exhibition->setRelation('applications', $applications);
        }

        $reservedCount = $exhibition->applications()
            ->reservesArtistPlace()
            ->count();
        $enrolledCount = $exhibition->applications()
            ->reservesArtistPlace()
            ->count();
        $pendingPaymentCount = $exhibition->applications()
            ->activePaymentReservation()
            ->count();

        $existingApplication = auth()->check()
            ? $exhibition->applications()
                ->where('user_id', auth()->id())
                ->first()
            : null;

        $registrationWindowOpen = $exhibition->status === 'published'
            && $exhibition->registrationIsOpen();
        $registrationOpen = $registrationWindowOpen
            && $reservedCount < $exhibition->max_artists;

        return view('artfy.exhibition-detail', compact(
            'exhibition',
            'reservedCount',
            'enrolledCount',
            'pendingPaymentCount',
            'existingApplication',
            'registrationOpen',
            'registrationWindowOpen',
            'visitOpen',
            'galleryOpen',
            'resultsVisible'
        ));
    }

    /**
     * Return available, admin-approved artworks for Discover and listings.
     */
    private function publicArtworkCollection(bool $includeSold = false): array
    {
        $viewer = auth()->user();
        $likedArtworkIds = $viewer?->isArtLover()
            ? $viewer->likedArtworks()->pluck('artworks.id')->flip()
            : collect();
        $savedArtworkIds = $viewer?->isArtLover()
            ? $viewer->savedArtworks()->pluck('artworks.id')->flip()
            : collect();

        return Artwork::with('artist.artistProfile')
            ->withCount('likes')
            ->whereIn(
                'status',
                $includeSold ? ['available', 'sold'] : ['available']
            )
            ->where('moderation_status', 'approved')
            ->latest()
            ->get()
            ->map(function (Artwork $artwork) use (
                $likedArtworkIds,
                $savedArtworkIds
            ): array {
                $artist = $artwork->artist;
                $profile = $artist?->artistProfile;

                return [
                    'id' => $artwork->id,
                    'slug' => 'uploaded-'.$artwork->id,
                    'title' => $artwork->title,
                    'artist' => $profile?->artist_name
                        ?: ($artist?->name ?? 'Artfy Artist'),
                    'artist_slug' => $artist
                        ? 'artist-'.$artist->id
                        : null,
                    'category' => $artwork->category ?: 'Other',
                    'type' => $artwork->medium ?: 'Not specified',
                    'artwork_type' => $artwork->artwork_type ?: 'physical',
                    'price' => $artwork->price,
                    'likes' => $artwork->likes_count,
                    'is_liked' => $likedArtworkIds->has($artwork->id),
                    'is_saved' => $savedArtworkIds->has($artwork->id),
                    'color' => 'green',
                    'description' => $artwork->description
                        ?: 'Explore this original artwork on Artfy.',
                    'image' => $artwork->image,
                    'is_uploaded' => true,
                    '_created_at' => $artwork->created_at?->timestamp ?? 0,
                ];
            })
            ->all();
    }

    /**
     * Return artists with approved applications and verified payments.
     */
    private function artistCollection(): array
    {
        return User::with([
            'artistProfile',
            'artistApplication',
        ])
            ->withCount('followers')
            ->where('role', 'artist')
            ->whereHas('artistApplication', function ($query) {
                $query->where('status', 'approved')
                    ->where('payment_status', 'verified');
            })
            ->latest()
            ->get()
            ->map(function (User $artist): array {
                $profile = $artist->artistProfile;
                $application = $artist->artistApplication;

                return [
                    'slug' => 'artist-'.$artist->id,
                    'name' => $profile?->artist_name ?: $artist->name,
                    'discipline' => $profile?->art_category
                        ?: $application?->art_category
                        ?: 'Independent artist',
                    'bio' => $profile?->bio
                        ?: $application?->biography
                        ?: 'Discover original artwork by this Artfy artist.',
                    'color' => 'green',
                    'image' => $profile?->profile_photo,
                    'follower_count' => (int) $artist->followers_count,
                    '_created_at' => $artist->created_at?->timestamp ?? 0,
                ];
            })
            ->all();
    }

    private function exhibitionCollection(): array
    {
        return Exhibition::query()
            ->where('status', 'published')
            ->withCount([
                'applications as reserved_artists_count' => fn ($query) => $query
                    ->reservesArtistPlace(),
            ])
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Exhibition $exhibition): array => [
                'id' => $exhibition->id,
                'slug' => $exhibition->slug,
                'title' => $exhibition->title,
                'subtitle' => $exhibition->subtitle
                    ?: $exhibition->starts_at->format('M j, Y'),
                'theme' => $exhibition->theme,
                'cover_image' => $exhibition->cover_image,
                'accent_color' => $exhibition->accent_color ?: '#55613a',
                'exhibition_state' => $exhibition->visitIsOpen()
                    ? 'Ongoing'
                    : (! $exhibition->visitHasStarted() ? 'Upcoming' : 'Recently closed'),
                'description' => $exhibition->description,
                'starts_at' => $exhibition->starts_at,
                'registration_fee' => $exhibition->registration_fee,
                'remaining_places' => max(
                    0,
                    $exhibition->max_artists - $exhibition->reserved_artists_count
                ),
                'color' => 'green',
            ])
            ->sortBy(fn (array $item): int => match ($item['exhibition_state']) {
                'Ongoing' => 0,
                'Upcoming' => 1,
                default => 2,
            })
            ->values()
            ->all();
    }

    private function discoverExhibitionAd(): ?array
    {
        $exhibition = Exhibition::query()
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if (! $exhibition) {
            return null;
        }

        $type = $exhibition->visitIsOpen()
            ? 'ongoing'
            : (! $exhibition->visitHasStarted() ? 'upcoming' : 'recently closed');

        return [
            'id' => $exhibition->id,
            'slug' => $exhibition->slug,
            'title' => $exhibition->title,
            'type' => $type,
            'subtitle' => $exhibition->subtitle
                ?: $exhibition->starts_at->format('M j, Y'),
            'description' => $exhibition->description,
            'cover_image' => $exhibition->cover_image,
            'starts_at' => $exhibition->starts_at,
            'ends_at' => $exhibition->ends_at,
        ];
    }
}
