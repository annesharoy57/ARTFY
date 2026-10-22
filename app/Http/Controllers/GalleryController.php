<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
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
        ]);
    }

    /**
     * Explore artworks with search, format, medium, price and sorting.
     */
    public function artworks(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        // Physical or digital artwork format.
        $artworkFormat = strtolower(trim(
            (string) $request->query('artwork_type', '')
        ));

        if (!in_array($artworkFormat, ['', 'physical', 'digital'], true)) {
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

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        $artworks = $this->publicArtworkCollection();

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

        // Keep the existing sorting options.
        switch ($sort) {
            case 'oldest':
                usort(
                    $artworks,
                    fn (array $a, array $b): int =>
                        ($a['_created_at'] ?? 0)
                        <=> ($b['_created_at'] ?? 0)
                );
                break;

            case 'popular':
                usort(
                    $artworks,
                    fn (array $a, array $b): int =>
                        ($b['likes'] ?? 0)
                        <=> ($a['likes'] ?? 0)
                );
                break;

            case 'price-low':
                usort(
                    $artworks,
                    fn (array $a, array $b): int =>
                        ($a['price'] ?? PHP_INT_MAX)
                        <=> ($b['price'] ?? PHP_INT_MAX)
                );
                break;

            case 'price-high':
                usort(
                    $artworks,
                    fn (array $a, array $b): int =>
                        ($b['price'] ?? -1)
                        <=> ($a['price'] ?? -1)
                );
                break;

            case 'latest':
            default:
                usort(
                    $artworks,
                    fn (array $a, array $b): int =>
                        ($b['_created_at'] ?? 0)
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

            // Retain this for any older code that still reads artType.
            'artType' => $mediumFilter,

            'maxPrice' => $maxPriceInput,
            'sort' => $sort,
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

        // Check both category and medium.
        $values = array_unique(array_filter([
            $category,
            $medium,
        ]));

        // Match medium names and common variations.
        $aliases = [
            'oil painting' => [
                'oil painting',
                'oil',
                'oil on canvas',
                'oil on linen',
                'oil on wood',
                'oil paint',
                'oil paints',
            ],

            'watercolor painting' => [
                'watercolor painting',
                'watercolour painting',
                'watercolor',
                'watercolour',
                'watercolor on paper',
                'watercolour on paper',
                'watercolor paint',
                'watercolour paint',
            ],

            'pastel painting' => [
                'pastel painting',
                'pastel',
                'soft pastel',
                'oil pastel',
                'pastels',
                'pastel on paper',
            ],

            'acrylic painting' => [
                'acrylic painting',
                'acrylic',
                'acrylic paint',
                'acrylic on canvas',
                'acrylic on paper',
            ],

            'ink wash painting' => [
                'ink wash painting',
                'ink wash',
                'ink wash art',
                'literati painting',
                'chinese ink painting',
                'sumi e',
                'sumi e painting',
            ],

            'encaustic painting' => [
                'encaustic painting',
                'encaustic',
                'hot wax painting',
                'hot wax',
                'wax painting',
            ],

            'spray painting' => [
                'spray painting',
                'spray paint',
                'spray art',
                'aerosol art',
            ],

            'sketching' => [
                'sketching',
                'sketch',
                'pencil sketch',
                'pen sketch',
                'charcoal sketch',
                'sketch drawing',
                'drawing',
            ],

            'photography' => [
                'photography',
                'photograph',
                'photo',
                'digital photography',
                'film photography',
                'photographic print',
            ],

            'digital painting' => [
                'digital painting',
                'digital art',
                'digital illustration',
                'digital drawing',
                'digital',
            ],
        ];

        $selectedAliases = $aliases[$selected] ?? [$selected];

        foreach ($values as $value) {
            // Exact match.
            if (in_array($value, $selectedAliases, true)) {
                return true;
            }

            // Match saved details such as "Original oil painting".
            foreach ($selectedAliases as $alias) {
                if (
                    $alias !== ''
                    && str_contains($value, $alias)
                ) {
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
        return view('artfy.listing', [
            'title' => 'Meet the artists',
            'eyebrow' => 'Creative minds',
            'description' => 'Get to know the people behind the art and explore their growing portfolios.',
            'pageType' => 'artists',
            'items' => $this->artistCollection(),
        ]);
    }

    /**
     * Display exhibitions.
     * No exhibition database model is connected yet.
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
     * Available and sold artworks remain viewable.
     * Only the owning artist can manage their own uploaded artwork.
     */
    public function artwork(string $slug): View
    {
        if (!preg_match('/^uploaded-(\d+)$/', $slug, $matches)) {
            abort(404);
        }

        $uploadedArtwork = Artwork::with('artist.artistProfile')
            ->whereIn('status', ['available', 'sold'])
            ->where('moderation_status', 'approved')
            ->find((int) $matches[1]);

        abort_unless($uploadedArtwork, 404);

        $artist = $uploadedArtwork->artist;
        $profile = $artist?->artistProfile;

        $artwork = [
            'id' => $uploadedArtwork->id,
            'slug' => 'uploaded-' . $uploadedArtwork->id,
            'title' => $uploadedArtwork->title,
            'description' => $uploadedArtwork->description
                ?: 'Explore this original artwork on Artfy.',
            'artist' => $profile?->artist_name
                ?: ($artist?->name ?? 'Artfy Artist'),
            'artist_slug' => $artist
                ? 'artist-' . $artist->id
                : null,
            'category' => $uploadedArtwork->category ?: 'Other',
            'type' => $uploadedArtwork->medium ?: 'Not specified',
            'price' => $uploadedArtwork->price,
            'likes' => 0,
            'color' => 'green',
            'image' => $uploadedArtwork->image,
            'is_uploaded' => true,
            'status' => $uploadedArtwork->status,
            'is_sold' => $uploadedArtwork->status === 'sold',

            // Show management controls only to the owning artist.
            'can_manage' => auth()->check()
                && (int) auth()->id() === (int) $uploadedArtwork->user_id
                && auth()->user()->isArtist(),
        ];

        return view('artfy.detail', [
            'title' => $artwork['title'],
            'eyebrow' => 'Original artwork',
            'description' => $artwork['description'],
            'item' => $artwork,
            'pageType' => 'artwork',
        ]);
    }

    /**
     * Display a public artist profile and portfolio.
     */
    public function artist(string $slug): View
    {
        if (!preg_match('/^artist-(\d+)$/', $slug, $matches)) {
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

        $profile = $artist->artistProfile;
        $application = $artist->artistApplication;

        $artworks = Artwork::where('user_id', $artist->id)
            ->whereIn('status', ['available', 'sold'])
            ->where('moderation_status', 'approved')
            ->latest()
            ->get();

        return view('artfy.artist-public-profile', [
            'artist' => $artist,
            'profile' => $profile,
            'application' => $application,
            'artworks' => $artworks,

            'artistName' => $profile?->artist_name
                ?: $artist->name,

            'discipline' => $profile?->art_category
                ?: $application?->art_category
                ?: 'Independent artist',

            'bio' => $profile?->bio
                ?: $application?->biography
                ?: 'This artist is part of the Artfy creative community.',

            'profilePhoto' => $profile?->profile_photo,
            'coverPhoto' => $profile?->cover_photo,
            'location' => $profile?->location,
            'portfolioUrl' => $profile?->portfolio_url,
        ]);
    }

    /**
     * Exhibition details.
     */
    public function exhibition(string $slug): View
    {
        abort(404);
    }

    /**
     * Return available, admin-approved artworks for Discover and listings.
     */
    private function publicArtworkCollection(): array
    {
        return Artwork::with('artist.artistProfile')
            ->where('status', 'available')
            ->where('moderation_status', 'approved')
            ->latest()
            ->get()
            ->map(function (Artwork $artwork): array {
                $artist = $artwork->artist;
                $profile = $artist?->artistProfile;

                return [
                    'id' => $artwork->id,
                    'slug' => 'uploaded-' . $artwork->id,
                    'title' => $artwork->title,
                    'artist' => $profile?->artist_name
                        ?: ($artist?->name ?? 'Artfy Artist'),
                    'artist_slug' => $artist
                        ? 'artist-' . $artist->id
                        : null,
                    'category' => $artwork->category ?: 'Other',
                    'type' => $artwork->medium ?: 'Not specified',

                    // Artwork format is separate from the artistic medium.
                    'artwork_type' => $artwork->artwork_type ?: 'physical',

                    'price' => $artwork->price,
                    'likes' => 0,
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
                    'slug' => 'artist-' . $artist->id,
                    'name' => $profile?->artist_name ?: $artist->name,
                    'discipline' => $profile?->art_category
                        ?: $application?->art_category
                        ?: 'Independent artist',
                    'bio' => $profile?->bio
                        ?: $application?->biography
                        ?: 'Discover original artwork by this Artfy artist.',
                    'color' => 'green',
                    'image' => $profile?->profile_photo,
                ];
            })
            ->all();
    }

    /**
     * Return no exhibitions until a real exhibition data source exists.
     */
    private function exhibitionCollection(): array
    {
        return [];
    }
}
