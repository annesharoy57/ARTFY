<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show notifications belonging to the signed-in user.
     *
     * Administrators use the standalone admin layout.
     * Artists and art lovers keep the existing Artfy layout.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15);

        if ($user->isAdmin()) {
            return view(
                'admin.notifications',
                compact('notifications')
            );
        }

        return view(
            'artfy.notifications.index',
            compact('notifications')
        );
    }

    /**
     * Mark one of the signed-in user's notifications as read
     * and redirect to its intended destination.
     */
    public function open(
        Request $request,
        string $notification
    ): RedirectResponse {
        $user = $request->user();

        // Only retrieve a notification belonging to this user.
        $userNotification = $user->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        // Mark the notification as read.
        $userNotification->markAsRead();

        $data = $userNotification->data ?? [];

        /*
         * Admin notifications should open their local destination
         * first, such as /admin/sales.
         */
        if ($user->isAdmin()) {
            $url = $data['url'] ?? null;

            if ($this->isSafeLocalUrl($url)) {
                return redirect($url);
            }

            // Fallback for an admin notification without a destination.
            return redirect()->route('admin.dashboard');
        }

        /*
         * Artist notifications can link directly to their artwork.
         */
        $artworkSlug = $data['artwork_slug'] ?? null;

        if (
            is_string($artworkSlug)
            && preg_match('/^uploaded-\d+$/', $artworkSlug)
        ) {
            return redirect()->route('artworks.show', [
                'slug' => $artworkSlug,
            ]);
        }

        // Handle other safe local destinations, if present.
        $url = $data['url'] ?? null;

        if ($this->isSafeLocalUrl($url)) {
            return redirect($url);
        }

        // Fallback for artists and art lovers.
        return redirect()->route('notifications.index');
    }

    /**
     * Accept only local relative paths.
     *
     * Reject external URLs, protocol-relative URLs and backslashes.
     */
    private function isSafeLocalUrl(mixed $url): bool
    {
        return is_string($url)
            && str_starts_with($url, '/')
            && !str_starts_with($url, '//')
            && !str_contains($url, '\\')
            && !preg_match('/[\x00-\x1F\x7F]/', $url);
    }
}
