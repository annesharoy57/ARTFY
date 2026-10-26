<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistProfileController extends Controller
{
    /**
     * Display the current user's profile.
     */
    public function show()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('home');
        }

        /*
         * ARTIST PROFILE
         * Artists keep their existing profile page and artwork sections.
         */
        if ($user->isArtist()) {
            $profile = $user->artistProfile;

            $artworks = Artwork::where('user_id', $user->id)
                ->latest()
                ->get();

            $soldArtworks = $artworks->filter(function ($artwork) {
                return (bool) $artwork->is_sold
                    || $artwork->status === 'sold';
            })->values();

            $availableArtworks = $artworks->filter(function ($artwork) {
                return !(bool) $artwork->is_sold
                    && $artwork->status === 'available'
                    && $artwork->moderation_status === 'approved';
            })->values();

            $otherArtworks = $artworks->filter(function ($artwork) {
                $isSold = (bool) $artwork->is_sold
                    || $artwork->status === 'sold';

                $isAvailable = !(bool) $artwork->is_sold
                    && $artwork->status === 'available'
                    && $artwork->moderation_status === 'approved';

                return !$isSold && !$isAvailable;
            })->values();

            $salesNotifications = Order::where('artist_id', $user->id)
                ->where('payment_status', 'verified')
                ->latest()
                ->get();

            $totalSales = $salesNotifications->count();

            return view('artfy.artist-profile-show', [
                'user' => $user,
                'profile' => $profile,
                'artworks' => $artworks,
                'availableArtworks' => $availableArtworks,
                'soldArtworks' => $soldArtworks,
                'otherArtworks' => $otherArtworks,
                'salesNotifications' => $salesNotifications,
                'totalSales' => $totalSales,
                'adminPreview' => false,
                'canManageArtistProfile' => true,
            ]);
        }

        /*
         * ART LOVER PROFILE
         * Art Lovers must never use the Artist Profile view.
         */
        if ($user->isArtLover()) {
            return view('artfy.lover-profile', [
                'user' => $user,
            ]);
        }

        /*
         * ADMIN PROFILE
         * Keep the existing artist-style preview for administrators.
         */
        if ($user->isAdmin()) {
            return view('artfy.artist-profile-show', [
                'user' => $user,
                'profile' => null,
                'artworks' => collect(),
                'availableArtworks' => collect(),
                'soldArtworks' => collect(),
                'otherArtworks' => collect(),
                'salesNotifications' => collect(),
                'totalSales' => 0,
                'adminPreview' => false,
                'canManageArtistProfile' => false,
            ]);
        }

        abort(403);
    }

    /**
     * Display the artist profile editing form.
     */
    public function edit()
    {
        $user = auth()->user();

        abort_unless($user && $user->isArtist(), 403);

        $profile = $user->artistProfile;

        return view('artfy.artist-profile', compact('user', 'profile'));
    }

    /**
     * Save artist profile changes.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        abort_unless($user && $user->isArtist(), 403);

        $validated = $request->validate([
            'artist_name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'art_category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'required_with:longitude',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'required_with:latitude',
            ],

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

                $validated[$field] = $request
                    ->file($field)
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

    /**
     * Display the Art Lover profile editing form.
     */
    public function editArtLover()
    {
        $user = auth()->user();

        abort_unless($user && $user->isArtLover(), 403);

        return view('artfy.lover-profile-edit', compact('user'));
    }

    /**
     * Save Art Lover profile changes.
     */
    public function updateArtLover(Request $request)
    {
        $user = auth()->user();

        abort_unless($user && $user->isArtLover(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],

            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $validated['profile_photo'] = $request
                ->file('profile_photo')
                ->store('user-profiles', 'public');
        }

        $user->update($validated);

        return redirect()
            ->route('my-profile')
            ->with(
                'success',
                'Your profile has been updated successfully!'
            );
    }
}
