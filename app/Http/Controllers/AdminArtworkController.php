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
     * Show artwork submissions and their moderation status.
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $status = $request->query('status', 'pending');

        if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $query = Artwork::with(['artist.artistProfile'])->latest();

        if ($status !== 'all') {
            $query->where('moderation_status', $status);
        }

        $artworks = $query->paginate(12)->withQueryString();

        $counts = [
            'pending' => Artwork::where('moderation_status', 'pending')->count(),
            'approved' => Artwork::where('moderation_status', 'approved')->count(),
            'rejected' => Artwork::where('moderation_status', 'rejected')->count(),
            'all' => Artwork::count(),
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

        // Notes are validated but not persisted yet because the
        // current artworks table has no admin_notes column.
        return back()->with(
            'success',
            'Artwork rejected. It has not been deleted.'
        );
    }
}
