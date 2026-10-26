<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ArtfyDatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the registration page for the selected account type.
     */
    public function showRegister(Request $request)
    {
        $role = $request->query('role', 'art_lover');

        if (!in_array($role, ['artist', 'art_lover'], true)) {
            return redirect()->route('auth.choose-role');
        }

        return view('auth.register', compact('role'));
    }

    /**
     * Register a new Artist or Art Lover.
     * Public registration cannot create an Admin account.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in(['artist', 'art_lover']),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->role = $validated['role'];

        if ($validated['role'] === 'artist') {
            $user->membership_status = 'pending';
        }

        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'artist') {
            return redirect()
                ->route('artist.application')
                ->with(
                    'success',
                    'Your artist account was created. Please complete your membership application and pay the annual fee through SSLCOMMERZ.'
                );
        }

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Your Artfy art-lover account was created successfully!'
            );
    }

    /**
     * Show the login page for the selected account type.
     */
    public function showLogin(Request $request)
    {
        $role = $request->query('role', 'art_lover');

        if (!in_array($role, ['admin', 'artist', 'art_lover'], true)) {
            return redirect()->route('auth.choose-role');
        }

        return view('auth.login', compact('role'));
    }

    /**
     * Authenticate the user and redirect according to account type.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in(['admin', 'artist', 'art_lover']),
            ],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Ensure the selected account type matches the user's account.
        if (!$user || $user->role !== $validated['role']) {
            return back()
                ->withErrors([
                    'email' => 'The email, password, or selected account type is incorrect.',
                ])
                ->withInput(
                    $request->only('email', 'role', 'remember')
                );
        }

        // Verify the password.
        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'The email, password, or selected account type is incorrect.',
                ])
                ->withInput(
                    $request->only('email', 'role', 'remember')
                );
        }

        // Regenerate the session after successful login.
        $request->session()->regenerate();
        $user = Auth::user();

        // Admins go to the Admin Dashboard.
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Artists with approved applications and verified payments
        // go to Discover, including older accounts with legacy
        // membership status or expiry values.
        if ($user->role === 'artist') {
            $application = $user->artistApplication;

            if (
                $application !== null
                && $application->status === 'approved'
                && $application->payment_status === 'verified'
            ) {
                return redirect()->route('home');
            }

            // Preserve existing membership checks for other artists.
            $expiresAt = $user->membership_expires_at;

            $membershipValid =
                $application !== null
                && $application->status === 'approved'
                && $application->payment_status === 'verified'
                && $user->membership_status === 'active'
                && $expiresAt !== null
                && $expiresAt->isFuture();

            if (!$membershipValid) {
                $message = 'Your Artist Studio access is not active yet. Please complete your application, pay through SSLCOMMERZ, and wait for Admin approval.';

                if (
                    $application
                    && $application->payment_status !== 'verified'
                ) {
                    $message = 'Please complete your membership payment through SSLCOMMERZ before Admin approval.';
                } elseif (
                    $application
                    && $application->status !== 'approved'
                ) {
                    $message = 'Your membership application is awaiting Admin approval.';
                } elseif (
                    $user->membership_status === 'active'
                    && $expiresAt !== null
                    && !$expiresAt->isFuture()
                ) {
                    $message = 'Your artist membership has expired. Please contact the Admin about renewal.';
                } elseif (
                    $application
                    && (
                        $user->membership_status !== 'active'
                        || $expiresAt === null
                    )
                ) {
                    $message = 'Your payment and application must be approved, and your membership must be activated by the Admin before you can access Artist Studio.';
                }

                return redirect()
                    ->route('artist.application')
                    ->with('success', $message);
            }

            return redirect()->route('home');
        }

        // Art lovers go to Discover.
        return redirect()->route('home');
    }

    /**
     * Show the Artist Membership Application page.
     */
    public function showArtistApplication()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'artist') {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Only artist accounts can apply for membership.'
                );
        }

        return view('auth.artist-application', [
            'application' => $user->artistApplication,
        ]);
    }

    /**
     * Save an Artist Membership Application.
     * Notify every admin after the application is saved successfully.
     */
    public function submitArtistApplication(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'artist') {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Only artist accounts can apply for membership.'
                );
        }

        // Prevent duplicate applications.
        if ($user->artistApplication) {
            return redirect()
                ->route('artist.application')
                ->with(
                    'success',
                    'You have already submitted your application.'
                );
        }

        $allowedCategories = [
            'Painting',
            'Photography',
            'Digital Art',
            'Illustration',
            'Sculpture',
            'Mixed Media',
            'Drawing',
            'Printmaking',
            'Crafts',
            'Other',
        ];

        // Validate the application form.
        $validated = $request->validate([
            'artist_name' => [
                'required',
                'string',
                'max:255',
            ],
            'biography' => [
                'required',
                'string',
                'max:5000',
            ],
            'art_categories' => [
                'required',
                'array',
                'min:1',
            ],
            'art_categories.*' => [
                'required',
                'string',
                'distinct',
                Rule::in($allowedCategories),
            ],
            'portfolio_url' => [
                'nullable',
                'url',
                'max:255',
            ],
        ]);

        // Annual membership fee: ৳1,000 by default.
        $fee = (int) config(
            'services.artfy_membership.annual_fee',
            1000
        );

        // Create the application. Payment is handled by SSLCOMMERZ.
        $application = $user->artistApplication()->create([
            'artist_name' => $validated['artist_name'],
            'biography' => $validated['biography'],
            'art_category' => implode(
                ', ',
                $validated['art_categories']
            ),
            'art_categories' => $validated['art_categories'],
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'membership_fee' => $fee,
            'payment_method' => 'sslcommerz',
            'payment_reference' => null,
            'payment_submitted_at' => null,
            'payment_verified_at' => null,
        ]);

        // Do not activate membership when applying.
        $user->membership_status = 'pending';
        $user->membership_expires_at = null;
        $user->save();

        // Notify all admins about the new artist application.
        User::where('role', 'admin')->get()->each(
            function (User $admin) use ($user, $application) {
                $admin->notify(new ArtfyDatabaseNotification(
                    'artfy.artist_application_submitted',
                    [
                        'title' => 'New artist membership application',
                        'message' => $user->name
                            . ' has submitted an artist membership application.',
                        'url' => route(
                            'admin.memberships.index',
                            [],
                            false
                        ),
                        'artist_id' => $user->id,
                        'artist_name' => $application->artist_name,
                        'application_id' => $application->id,
                    ]
                ));
            }
        );

        return redirect()
            ->route('artist.application')
            ->with(
                'success',
                'Your application has been submitted. Please pay the annual membership fee through SSLCOMMERZ. Artist Studio access begins only after Admin approval.'
            );
    }

    /**
     * Legacy manual payment endpoint.
     * Membership payments must go through SSLCOMMERZ.
     */
    public function submitMembershipPayment(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'artist') {
            abort(403, 'Only artist accounts can access membership payment.');
        }

        return redirect()
            ->route('artist.application')
            ->with(
                'error',
                'Manual membership payments are no longer accepted. Please use SSLCOMMERZ checkout.'
            );
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'You have logged out successfully.'
            );
    }
}
