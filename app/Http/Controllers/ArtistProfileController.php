<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistProfileController extends Controller
{
    /**
     * Show the signed-in user's profile.
     */
    public function show()
    {
        $user = auth()->user();

        // Artist profile and artwork grid.
        if ($user->isArtist()) {
            $profile = $user->artistProfile;

            $artworks = Artwork::where('user_id', $user->id)
                ->latest()
                ->get();

            return view(
                'artfy.artist-profile-show',
                compact('user', 'profile', 'artworks')
            );
        }

        // Art lovers can see their own personal profile.
        if ($user->isArtLover()) {
            return view('artfy.lover-profile', compact('user'));
        }

        // Admins should use their admin dashboard.
        abort_unless($user->isArtist() || $user->isArtLover(), 403);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Show the artist's profile editing form.
     */
    public function edit()
    {
        $user = auth()->user();

        abort_unless($user->isArtist(), 403);

        $profile = $user->artistProfile;

        return view('artfy.artist-profile', compact('user', 'profile'));
    }

    /**
     * Save changes to the artist's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->isArtist(), 403);

        $validated = $request->validate([
            'artist_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'art_category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'cover_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $profile = $user->artistProfile;

        foreach (['profile_photo', 'cover_photo'] as $field) {
            if ($request->hasFile($field)) {
                if ($profile && $profile->{$field}) {
                    Storage::disk('public')->delete($profile->{$field});
                }

                $validated[$field] = $request->file($field)
                    ->store('artist-profiles', 'public');
            }
        }

        $user->artistProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return redirect()
            ->route('artist-profile.edit')
            ->with('success', 'Your profile has been saved successfully!');
    }
}
