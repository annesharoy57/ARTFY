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
     * Display an artist's My Profile interface in admin preview mode.
     */
    public function showArtist(User $user)
    {
        $this->checkAdmin();

        abort_unless($user->isArtist(), 404);

        $user->load('artistProfile');

        $profile = $user->artistProfile;

        $artworks = Artwork::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('artfy.artist-profile-show', [
            'user' => $user,
            'profile' => $profile,
            'artworks' => $artworks,
            'adminPreview' => true,
        ]);
    }

    /**
     * Display one art lover's profile page.
     */
    public function showArtLover(User $user)
    {
        $this->checkAdmin();

        abort_unless($user->isArtLover(), 404);

        return view('admin.users.art-lover-show', [
            'artLover' => $user,
        ]);
    }
}
