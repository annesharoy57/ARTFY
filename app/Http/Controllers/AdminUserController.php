<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Ensure only administrators can access these pages.
     */
    private function checkAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->isAdmin(),
            403,
            'Only administrators can access this page.'
        );
    }

    /**
     * Display all artists.
     */
    public function artists()
    {
        $this->checkAdmin();

        $artists = User::with(['artistProfile', 'artistApplication'])
            ->where('role', 'artist')
            ->latest()
            ->paginate(12);

        return view('admin.users.artists', compact('artists'));
    }

    /**
     * Display all art lovers.
     */
    public function artLovers()
    {
        $this->checkAdmin();

        $artLovers = User::where('role', 'art_lover')
            ->latest()
            ->paginate(12);

        return view('admin.users.art-lovers', compact('artLovers'));
    }

    /**
     * Display an artist's profile in admin preview mode.
     *
     * Hide only artwork ID 7 titled "Velvet Afterglow".
     * Do not delete the artwork or modify any order/payment data.
     */
    public function showArtist(User $user)
    {
        $this->checkAdmin();

        abort_unless($user->isArtist(), 404);

        $user->load('artistProfile');

        $profile = $user->artistProfile;

        // Exclude only the specified artwork from this profile preview.
        $artworks = Artwork::where('user_id', $user->id)
            ->whereNot(function ($query) {
                $query->where('id', 7)
                    ->where('title', 'Velvet Afterglow');
            })
            ->latest()
            ->get();

        // Sold artworks.
        $soldArtworks = $artworks->filter(function ($artwork) {
            return (bool) $artwork->is_sold
                || $artwork->status === 'sold';
        })->values();

        // Approved and available artworks.
        $availableArtworks = $artworks->filter(function ($artwork) {
            return !(bool) $artwork->is_sold
                && $artwork->status === 'available'
                && $artwork->moderation_status === 'approved';
        })->values();

        // Artworks that are neither sold nor approved and available.
        $otherArtworks = $artworks->filter(function ($artwork) {
            $isSold = (bool) $artwork->is_sold
                || $artwork->status === 'sold';

            $isAvailable = !(bool) $artwork->is_sold
                && $artwork->status === 'available'
                && $artwork->moderation_status === 'approved';

            return !$isSold && !$isAvailable;
        })->values();

        return view('artfy.artist-profile-show', [
            'user' => $user,
            'profile' => $profile,
            'artworks' => $artworks,
            'availableArtworks' => $availableArtworks,
            'soldArtworks' => $soldArtworks,
            'otherArtworks' => $otherArtworks,
            'salesNotifications' => collect(),
            'totalSales' => $soldArtworks->count(),
            'adminPreview' => true,
            'canManageArtistProfile' => false,
        ]);
    }

    /**
     * Display an art lover's personal profile in admin preview mode.
     */
    public function showArtLover(User $user)
    {
        $this->checkAdmin();

        abort_unless($user->isArtLover(), 404);

        return view('artfy.lover-profile', [
            'user' => $user,
            'adminPreview' => true,
        ]);
    }
}
