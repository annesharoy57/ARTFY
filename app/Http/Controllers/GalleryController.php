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
     * Explore artworks with search, art type, price and sorting.
     */
    public function artworks(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $artType = trim((string) $request->query('art_type', ''));
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
                $artType,
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

                $matchesArtType = $artType === ''
                    || $this->matchesArtType($artwork, $artType);

                $price = $artwork['price'] ?? null;

                $matchesPrice = $maxPrice === null
                    || (
                        $price !== null
                        && (float) $price <= $maxPrice
                    );

                return $matchesQuery
                    && $matchesArtType
                    && $matchesPrice;
            }
        ));

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
            'artType' => $artType,
            'maxPrice' => $maxPriceInput,
            'sort' => $sort,
        ]);
    }

    /**
     * Match the art-type dropdown against category and medium.
     */
    private function matchesArtType(
        array $artwork,
        string $selectedType
    ): bool {
        $category = mb_strtolower(trim(
            (string) ($artwork['category'] ?? '')
        ));

        $medium = mb_strtolower(trim(
            (string) ($artwork['type'] ?? '')
        ));

        $selected = mb_strtolower(trim($selectedType));

        $normalize = static function (string $value): string {
            return trim(
                preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? ''
            );
        };

        $category = $normalize($category);
        $medium = $normalize($medium);
        $selected = $normalize($selected);

        if ($selected === 'oil painting') {
            return $medium === 'oil'
                || $medium === 'oil painting'
                || $category === 'oil painting';
        }

        if ($selected === 'digital painting') {
            return $medium === 'digital painting'
                || (
                    $medium === 'digital'
                    && $category === 'painting'
                );
        }

        if ($selected === 'watercolor painting') {
            return in_array($medium, [
                'watercolor',
                'watercolour',
                'watercolor painting',
                'watercolour painting',
            ], true);
        }

        if ($selected === 'sketching') {
            return in_array(
                $category,
                ['sketching', 'sketch', 'drawing'],
                true
            ) || in_array(
                $medium,
                ['sketching', 'sketch', 'drawing'],
                true
            );
        }

        if ($selected === 'drawing') {
            return in_array(
                $category,
                ['drawing', 'sketching', 'sketch'],
                true
            ) || in_array(
                $medium,
                ['drawing', 'sketching', 'sketch'],
                true
            );
        }

        return $category === $selected
            || $medium === $selected;
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
     * A separate purchase workflow must prevent buying sold items.
     */
    public function artwork(string $slug): View
    {
        if (!preg_match('/^uploaded-(\d+)$/', $slug, $matches)) {
            abort(404);
        }

        // FIX: Allow both available and sold artworks to be viewed.
        $uploadedArtwork = Artwork::with('artist.artistProfile')
            ->whereIn('status', ['available', 'sold'])
            ->where('moderation_status', 'approved')
            ->find((int) $matches[1]);

        abort_unless($uploadedArtwork, 404);

        $artist = $uploadedArtwork->artist;
        $profile = $artist?->artistProfile;

        $artwork = [
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

            // Lets the view distinguish sold items from available items.
            'status' => $uploadedArtwork->status,
            'is_sold' => $uploadedArtwork->status === 'sold',
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
                    'slug' => 'uploaded-' . $artwork->id,
                    'title' => $artwork->title,
                    'artist' => $profile?->artist_name
                        ?: ($artist?->name ?? 'Artfy Artist'),
                    'artist_slug' => $artist
                        ? 'artist-' . $artist->id
                        : null,
                    'category' => $artwork->category ?: 'Other',
                    'type' => $artwork->medium ?: 'Not specified',
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
