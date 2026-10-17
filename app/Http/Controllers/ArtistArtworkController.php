<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistArtworkController extends Controller
{
    private function checkArtistAccess()
    {
        $user = auth()->user();

        if (!$user || !$user->isArtist()) {
            return redirect()
                ->route('home')
                ->with('error', 'Artist accounts only.');
        }

        $application = $user->artistApplication;

        if (
            !$application ||
            $application->status !== 'approved' ||
            $application->payment_status !== 'verified'
        ) {
            return redirect()
                ->route('artist.application')
                ->with(
                    'error',
                    'Your artist membership must be approved and your payment verified.'
                );
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        // Show all of this artist's artworks, including pending
        // and rejected works, so the artist can see their status.
        $artworks = Artwork::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('artfy.artist-studio', compact('artworks'));
    }

    public function create()
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        return view('artfy.artwork-create');
    }

    public function store(Request $request)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'medium' => ['nullable', 'string', 'max:255'],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999.99',
            ],
        ]);

        $validated['image'] = $request->file('image')
            ->store('artworks', 'public');

        $validated['user_id'] = auth()->id();

        // Availability and admin review are separate statuses.
        $validated['status'] = 'available';
        $validated['moderation_status'] = 'pending';
        $validated['is_sold'] = false;

        Artwork::create($validated);

        return redirect()
            ->route('artist.studio')
            ->with(
                'success',
                'Your artwork was uploaded and is waiting for admin approval.'
            );
    }

    public function destroy(Artwork $artwork)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        // Artists may delete only their own artworks.
        abort_unless(
            (int) $artwork->user_id === (int) auth()->id(),
            403
        );

        if ($artwork->image) {
            Storage::disk('public')->delete($artwork->image);
        }

        $artwork->delete();

        return redirect()
            ->route('artist.studio')
            ->with('success', 'Artwork deleted successfully.');
    }
}