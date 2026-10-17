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

        if (!$user->isArtist()) {
            return redirect()->route('home')
                ->with('error', 'Artist accounts only.');
        }

        $application = $user->artistApplication;

        if (
            !$application ||
            $application->status !== 'approved' ||
            $application->payment_status !== 'verified'
        ) {
            return redirect()->route('artist.application')
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
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $validated['image'] = $request->file('image')
            ->store('artworks', 'public');

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'available';
        $validated['is_sold'] = false;

        Artwork::create($validated);

        return redirect()
            ->route('artist.studio')
            ->with('success', 'Your artwork has been uploaded successfully!');
    }

    public function destroy(Artwork $artwork)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        abort_unless($artwork->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($artwork->image);
        $artwork->delete();

        return redirect()
            ->route('artist.studio')
            ->with('success', 'Artwork deleted successfully.');
    }
}
