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
     * Keep the existing homepage structure and sample content.
     */
    public function home(): View
    {
        $artworks = array_merge(
            $this->publicArtworkCollection(),
            array_slice($this->artworkCollection(), 0, 3)
        );

        return view('artfy.home', [
            'artworks' => array_slice($artworks, 0, 3),
            'artists' => $this->artistCollection(),
            'exhibitions' => $this->exhibitionCollection(),
        ]);
    }

    /**
     * Explore artworks with combined search, art type, budget and sorting.
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

        $artworks = array_merge(
            $this->publicArtworkCollection(),
            $this->artworkCollection()
        );

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
     * Match one combined art-type dropdown against category and medium.
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
     * Artists listing.
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
     * Exhibitions listing.
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
     * Artwork details.
     * Uploaded artworks are visible only after admin approval.
     */
    public function artwork(string $slug): View
    {
        if (preg_match('/^uploaded-(\d+)$/', $slug, $matches)) {
            $uploadedArtwork = Artwork::with('artist.artistProfile')
                ->where('status', 'available')
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
            ];

            return view('artfy.detail', [
                'title' => $artwork['title'],
                'eyebrow' => 'Original artwork',
                'description' => $artwork['description'],
                'item' => $artwork,
                'pageType' => 'artwork',
            ]);
        }

        // Preserve existing sample artwork URLs.
        $artwork = collect($this->artworkCollection())
            ->firstWhere('slug', $slug);

        abort_unless($artwork, 404);

        $artwork['is_uploaded'] = false;

        return view('artfy.detail', [
            'title' => $artwork['title'],
            'eyebrow' => 'Artwork details',
            'description' => $artwork['description'],
            'item' => $artwork,
            'pageType' => 'artwork',
        ]);
    }

    /**
     * Artist details and portfolio.
     */
    public function artist(string $slug): View
    {
        if (preg_match('/^artist-(\d+)$/', $slug, $matches)) {
            $artist = User::with([
                'artistProfile',
                'artistApplication',
            ])
                ->where('role', 'artist')
                ->whereHas('artistApplication', function ($query) {
                    $query->where('status', 'approved')
                        ->where('payment_status', 'verified');
                })
                ->find((int) $matches[1]);

            abort_unless($artist, 404);

            $profile = $artist->artistProfile;
            $application = $artist->artistApplication;

            $item = [
                'slug' => 'artist-' . $artist->id,
                'name' => $profile?->artist_name ?: $artist->name,
                'discipline' => $profile?->art_category
                    ?: 'Independent artist',
                'bio' => $profile?->bio
                    ?: $application?->biography
                    ?: 'Discover original artwork by this Artfy artist.',
                'image' => $profile?->profile_photo,
                'color' => 'green',
            ];

            return view('artfy.detail', [
                'title' => $item['name'],
                'eyebrow' => 'Artist portfolio',
                'description' => $item['bio'],
                'item' => $item,
                'pageType' => 'artist',
            ]);
        }

        // Preserve existing sample artist URLs.
        $artist = collect($this->sampleArtistCollection())
            ->firstWhere('slug', $slug);

        abort_unless($artist, 404);

        return view('artfy.detail', [
            'title' => $artist['name'],
            'eyebrow' => 'Artist portfolio',
            'description' => $artist['bio'],
            'item' => $artist,
            'pageType' => 'artist',
        ]);
    }

    /**
     * Exhibition details.
     */
    public function exhibition(string $slug): View
    {
        $exhibition = collect($this->exhibitionCollection())
            ->firstWhere('slug', $slug);

        abort_unless($exhibition, 404);

        return view('artfy.detail', [
            'title' => $exhibition['title'],
            'eyebrow' => 'Online exhibition',
            'description' => $exhibition['description'],
            'item' => $exhibition,
            'pageType' => 'exhibition',
        ]);
    }

    /**
     * Database artworks approved by the admin and currently available.
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
     * Approved artists followed by the sample artists.
     */
    private function artistCollection(): array
    {
        $databaseArtists = User::with([
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

        return array_merge(
            $databaseArtists,
            $this->sampleArtistCollection()
        );
    }

    /**
     * Existing demo artworks.
     * Prices here are sample amounts in BDT.
     */
    private function artworkCollection(): array
    {
        return [
            [
                'slug' => 'quiet-morning',
                'title' => 'Quiet Morning',
                'artist' => 'Maya Chen',
                'artist_slug' => 'maya-chen',
                'category' => 'Painting',
                'type' => 'Acrylic',
                'price' => 2400,
                'likes' => 128,
                'color' => 'peach',
                'description' => 'A soft study of the first light of day, painted in warm, gentle layers.',
                '_created_at' => 0,
            ],
            [
                'slug' => 'blue-hour',
                'title' => 'Blue Hour',
                'artist' => 'Leo Martin',
                'artist_slug' => 'leo-martin',
                'category' => 'Photography',
                'type' => 'Digital',
                'price' => 1800,
                'likes' => 96,
                'color' => 'blue',
                'description' => 'A quiet city street caught between the last light of day and the first stars.',
                '_created_at' => 0,
            ],
            [
                'slug' => 'wild-garden',
                'title' => 'Wild Garden',
                'artist' => 'Amara Okafor',
                'artist_slug' => 'amara-okafor',
                'category' => 'Illustration',
                'type' => 'Digital',
                'price' => 1250,
                'likes' => 164,
                'color' => 'green',
                'description' => 'A lively imagined garden filled with color, movement, and small hidden details.',
                '_created_at' => 0,
            ],
            [
                'slug' => 'soft-forms',
                'title' => 'Soft Forms',
                'artist' => 'Maya Chen',
                'artist_slug' => 'maya-chen',
                'category' => 'Painting',
                'type' => 'Oil',
                'price' => 3200,
                'likes' => 82,
                'color' => 'lilac',
                'description' => 'An abstract composition exploring balance, shape, and a quieter kind of color.',
                '_created_at' => 0,
            ],
        ];
    }

    /**
     * Existing demo artists.
     */
    private function sampleArtistCollection(): array
    {
        return [
            [
                'slug' => 'maya-chen',
                'name' => 'Maya Chen',
                'discipline' => 'Painter · Portland, OR',
                'bio' => 'Maya creates intimate, color-rich paintings inspired by everyday moments and the changing light.',
                'color' => 'peach',
            ],
            [
                'slug' => 'leo-martin',
                'name' => 'Leo Martin',
                'discipline' => 'Photographer · London, UK',
                'bio' => 'Leo finds small stories in city streets, late evenings, and the spaces people pass through.',
                'color' => 'blue',
            ],
            [
                'slug' => 'amara-okafor',
                'name' => 'Amara Okafor',
                'discipline' => 'Illustrator · Lagos, NG',
                'bio' => 'Amara makes expressive digital illustrations inspired by nature, memory, and imagined worlds.',
                'color' => 'green',
            ],
        ];
    }

    /**
     * Existing demo exhibitions.
     */
    private function exhibitionCollection(): array
    {
        return [
            [
                'slug' => 'color-in-motion',
                'title' => 'Color in Motion',
                'subtitle' => 'A study in energy and expression',
                'description' => 'A bright collection of works where color takes the lead and every piece has its own rhythm.',
                'color' => 'peach',
            ],
            [
                'slug' => 'quiet-spaces',
                'title' => 'Quiet Spaces',
                'subtitle' => 'Finding calm in the everyday',
                'description' => 'Artists share the small, peaceful moments that make an ordinary place feel like home.',
                'color' => 'blue',
            ],
            [
                'slug' => 'nature-reimagined',
                'title' => 'Nature, Reimagined',
                'subtitle' => 'A new perspective on the natural world',
                'description' => 'A playful collection that transforms familiar plants, landscapes, and living forms.',
                'color' => 'green',
            ],
        ];
    }
}
