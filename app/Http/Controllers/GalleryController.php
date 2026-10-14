<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function home(): View
    {
        return view('artfy.home', [
            'artworks' => array_slice($this->artworkCollection(), 0, 3),
            'artists' => $this->artistCollection(),
            'exhibitions' => $this->exhibitionCollection(),
        ]);
    }

    public function artworks(Request $request): View
    {
        $artworks = $this->artworkCollection();
        $query = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $type = (string) $request->query('type', '');
        $sort = (string) $request->query('sort', 'latest');

        $artworks = array_values(array_filter($artworks, function (array $artwork) use ($query, $category, $type): bool {
            $matchesQuery = $query === ''
                || str_contains(strtolower($artwork['title'].' '.$artwork['artist']), strtolower($query));

            return $matchesQuery
                && ($category === '' || $artwork['category'] === $category)
                && ($type === '' || $artwork['type'] === $type);
        }));

        if ($sort === 'popular') {
            usort($artworks, fn (array $a, array $b): int => $b['likes'] <=> $a['likes']);
        } elseif ($sort === 'price-low') {
            usort($artworks, fn (array $a, array $b): int => $a['price'] <=> $b['price']);
        }

        return view('artfy.listing', [
            'title' => 'Explore artworks',
            'eyebrow' => 'The Artfy collection',
            'description' => 'Discover original work from independent artists, all in one place.',
            'pageType' => 'artworks',
            'items' => $artworks,
            'query' => $query,
            'category' => $category,
            'type' => $type,
            'sort' => $sort,
        ]);
    }

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

    public function artwork(string $slug): View
    {
        $artwork = collect($this->artworkCollection())->firstWhere('slug', $slug);
        abort_unless($artwork, 404);

        return view('artfy.detail', [
            'title' => $artwork['title'],
            'eyebrow' => 'Artwork details',
            'description' => $artwork['description'],
            'item' => $artwork,
            'pageType' => 'artwork',
        ]);
    }

    public function artist(string $slug): View
    {
        $artist = collect($this->artistCollection())->firstWhere('slug', $slug);
        abort_unless($artist, 404);

        return view('artfy.detail', [
            'title' => $artist['name'],
            'eyebrow' => 'Artist portfolio',
            'description' => $artist['bio'],
            'item' => $artist,
            'pageType' => 'artist',
        ]);
    }

    public function exhibition(string $slug): View
    {
        $exhibition = collect($this->exhibitionCollection())->firstWhere('slug', $slug);
        abort_unless($exhibition, 404);

        return view('artfy.detail', [
            'title' => $exhibition['title'],
            'eyebrow' => 'Online exhibition',
            'description' => $exhibition['description'],
            'item' => $exhibition,
            'pageType' => 'exhibition',
        ]);
    }

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
                'price' => 240,
                'likes' => 128,
                'color' => 'peach',
                'description' => 'A soft study of the first light of day, painted in warm, gentle layers.',
            ],
            [
                'slug' => 'blue-hour',
                'title' => 'Blue Hour',
                'artist' => 'Leo Martin',
                'artist_slug' => 'leo-martin',
                'category' => 'Photography',
                'type' => 'Digital',
                'price' => 180,
                'likes' => 96,
                'color' => 'blue',
                'description' => 'A quiet city street caught between the last light of day and the first stars.',
            ],
            [
                'slug' => 'wild-garden',
                'title' => 'Wild Garden',
                'artist' => 'Amara Okafor',
                'artist_slug' => 'amara-okafor',
                'category' => 'Illustration',
                'type' => 'Digital',
                'price' => 125,
                'likes' => 164,
                'color' => 'green',
                'description' => 'A lively imagined garden filled with color, movement, and small hidden details.',
            ],
            [
                'slug' => 'soft-forms',
                'title' => 'Soft Forms',
                'artist' => 'Maya Chen',
                'artist_slug' => 'maya-chen',
                'category' => 'Painting',
                'type' => 'Oil',
                'price' => 320,
                'likes' => 82,
                'color' => 'lilac',
                'description' => 'An abstract composition exploring balance, shape, and a quieter kind of color.',
            ],
        ];
    }

    private function artistCollection(): array
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
