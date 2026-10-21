<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ArtistArtworkController extends Controller
{
    /**
     * Verify that the current user is an approved artist
     * with verified membership payment.
     */
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

    /**
     * Show the current artist's artworks.
     */
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

    /**
     * Show the artwork upload form.
     */
    public function create()
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        return view('artfy.artwork-create');
    }

    /**
     * Save a newly uploaded artwork.
     */
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

            'artwork_type' => [
                'required',
                'in:physical,digital',
            ],

            'digital_file' => [
                'required_if:artwork_type,digital',
                'prohibited_unless:artwork_type,digital',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,zip',
                'max:20480',
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

        $imagePath = $request->file('image')->store(
            'artworks',
            'public'
        );

        if (!$imagePath) {
            throw new RuntimeException(
                'The artwork image could not be stored.'
            );
        }

        $digitalFilePath = null;

        try {
            if ($validated['artwork_type'] === 'digital') {
                $digitalFilePath = Storage::disk('local')->putFile(
                    'digital-artworks',
                    $request->file('digital_file')
                );

                if (!$digitalFilePath) {
                    throw new RuntimeException(
                        'The digital artwork file could not be stored.'
                    );
                }
            }

            Artwork::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'category' => $validated['category'] ?? null,
                'medium' => $validated['medium'] ?? null,
                'price' => $validated['price'] ?? null,
                'artwork_type' => $validated['artwork_type'],
                'digital_file_path' => $digitalFilePath,
                'status' => 'available',
                'moderation_status' => 'pending',
                'is_sold' => false,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($imagePath);

            if ($digitalFilePath) {
                Storage::disk('local')->delete($digitalFilePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('my-profile')
            ->with(
                'success',
                'Your artwork was uploaded and is waiting for admin approval.'
            );
    }

    /**
     * Show the edit form for an artwork owned by the current artist.
     */
    public function edit(Artwork $artwork)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        abort_unless(
            (int) $artwork->user_id === (int) auth()->id(),
            403
        );

        if (
            $artwork->status === 'sold' ||
            (bool) $artwork->is_sold ||
            Order::where('artwork_id', $artwork->id)->exists()
        ) {
            return redirect()
                ->route('my-profile')
                ->with(
                    'error',
                    'This artwork has an order record and can no longer be edited.'
                );
        }

        return view('artfy.artwork-edit', compact('artwork'));
    }

    /**
     * Update an artwork and submit it for moderation again.
     */
    public function update(Request $request, Artwork $artwork)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        abort_unless(
            (int) $artwork->user_id === (int) auth()->id(),
            403
        );

        if (
            $artwork->status === 'sold' ||
            (bool) $artwork->is_sold ||
            Order::where('artwork_id', $artwork->id)->exists()
        ) {
            return redirect()
                ->route('my-profile')
                ->with(
                    'error',
                    'This artwork has an order record and can no longer be edited.'
                );
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'artwork_type' => [
                'required',
                'in:physical,digital',
            ],

            'digital_file' => [
                'nullable',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp,zip',
                'max:20480',
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

        if (
            $validated['artwork_type'] === 'digital' &&
            !$request->hasFile('digital_file') &&
            !$artwork->digital_file_path
        ) {
            return back()
                ->withErrors([
                    'digital_file' =>
                        'Upload a digital file for this artwork.',
                ])
                ->withInput();
        }

        $oldImagePath = $artwork->image;
        $oldDigitalFilePath = $artwork->digital_file_path;

        $newImagePath = null;
        $newDigitalFilePath = null;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store(
                    'artworks',
                    'public'
                );

                if (!$newImagePath) {
                    throw new RuntimeException(
                        'The replacement image could not be stored.'
                    );
                }
            }

            if (
                $validated['artwork_type'] === 'digital' &&
                $request->hasFile('digital_file')
            ) {
                $newDigitalFilePath = Storage::disk('local')->putFile(
                    'digital-artworks',
                    $request->file('digital_file')
                );

                if (!$newDigitalFilePath) {
                    throw new RuntimeException(
                        'The replacement digital file could not be stored.'
                    );
                }
            }

            $artwork->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image' => $newImagePath ?? $oldImagePath,
                'category' => $validated['category'] ?? null,
                'medium' => $validated['medium'] ?? null,
                'price' => $validated['price'] ?? null,
                'artwork_type' => $validated['artwork_type'],

                'digital_file_path' =>
                    $validated['artwork_type'] === 'digital'
                        ? ($newDigitalFilePath ?? $oldDigitalFilePath)
                        : null,

                // Require admin review after an edit.
                'moderation_status' => 'pending',
            ]);
        } catch (Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            if ($newDigitalFilePath) {
                Storage::disk('local')->delete($newDigitalFilePath);
            }

            throw $exception;
        }

        // Delete replaced files only after the database update succeeds.
        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        if (
            $validated['artwork_type'] !== 'digital' &&
            $oldDigitalFilePath
        ) {
            Storage::disk('local')->delete($oldDigitalFilePath);
        } elseif ($newDigitalFilePath && $oldDigitalFilePath) {
            Storage::disk('local')->delete($oldDigitalFilePath);
        }

        return redirect()
            ->route('my-profile')
            ->with(
                'success',
                'Artwork updated successfully and submitted for admin review.'
            );
    }

    /**
     * Delete an artwork owned by the current artist.
     */
    public function destroy(Artwork $artwork)
    {
        if ($redirect = $this->checkArtistAccess()) {
            return $redirect;
        }

        abort_unless(
            (int) $artwork->user_id === (int) auth()->id(),
            403
        );

        if (
            $artwork->status === 'sold' ||
            (bool) $artwork->is_sold ||
            Order::where('artwork_id', $artwork->id)->exists()
        ) {
            return redirect()
                ->route('my-profile')
                ->with(
                    'error',
                    'This artwork has an order record and cannot be deleted.'
                );
        }

        $imagePath = $artwork->image;
        $digitalFilePath = $artwork->digital_file_path;

        // Delete the database record before removing stored files.
        $artwork->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        if ($digitalFilePath) {
            Storage::disk('local')->delete($digitalFilePath);
        }

        return redirect()
            ->route('my-profile')
            ->with('success', 'Artwork deleted successfully.');
    }
}
