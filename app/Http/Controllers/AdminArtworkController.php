<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminArtworkController extends Controller
{
    private function checkAdmin(): void
    {
        abort_unless(
            Auth::check() && Auth::user()->isAdmin(),
            403,
            'Only administrators can manage artwork reviews.'
        );
    }

    /**
     * Base query for artworks visible in the admin review page.
     *
     * Excludes only artwork ID 7 titled "Velvet Afterglow".
     * The database record remains untouched.
     */
    private function visibleArtworksQuery()
    {
        return Artwork::query()->where(function ($query) {
            $query->where('id', '!=', 7)
                ->orWhere('title', '!=', 'Velvet Afterglow');
        });
    }

    /**
     * Show artwork submissions and their moderation status.
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $status = $request->query('status', 'pending');

        if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        // Exclude the specified artwork from every review tab.
        $query = $this->visibleArtworksQuery()
            ->with(['artist.artistProfile'])
            ->latest();

        if ($status !== 'all') {
            $query->where('moderation_status', $status);
        }

        $artworks = $query->paginate(12)->withQueryString();

        // All review counts exclude the same artwork.
        $counts = [
            'pending' => (clone $this->visibleArtworksQuery())
                ->where('moderation_status', 'pending')
                ->count(),

            'approved' => (clone $this->visibleArtworksQuery())
                ->where('moderation_status', 'approved')
                ->count(),

            'rejected' => (clone $this->visibleArtworksQuery())
                ->where('moderation_status', 'rejected')
                ->count(),

            'all' => $this->visibleArtworksQuery()->count(),
        ];

        return view('admin.artworks.index', compact(
            'artworks',
            'status',
            'counts'
        ));
    }

    /**
     * Approve an artwork awaiting review.
     */
    public function approve(Artwork $artwork)
    {
        $this->checkAdmin();

        // Prevent the hidden artwork from being reviewed here.
        abort_if(
            (int) $artwork->id === 7
                && $artwork->title === 'Velvet Afterglow',
            404
        );

        if ($artwork->moderation_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending artworks can be approved.'
            );
        }

        $artwork->moderation_status = 'approved';
        $artwork->save();

        return back()->with(
            'success',
            'Artwork approved successfully.'
        );
    }

    /**
     * Reject an artwork awaiting review.
     */
    public function reject(Request $request, Artwork $artwork)
    {
        $this->checkAdmin();

        // Prevent the hidden artwork from being reviewed here.
        abort_if(
            (int) $artwork->id === 7
                && $artwork->title === 'Velvet Afterglow',
            404
        );

        if ($artwork->moderation_status !== 'pending') {
            return back()->with(
                'error',
                'Only pending artworks can be rejected.'
            );
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        $artwork->moderation_status = 'rejected';
        $artwork->save();

        // Notes are validated but not persisted because the current
        // artworks table has no admin_notes column.
        return back()->with(
            'success',
            'Artwork rejected. It has not been deleted.'
        );
    }
}
