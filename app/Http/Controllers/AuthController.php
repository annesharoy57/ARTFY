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

        if (! in_array($role, ['artist', 'art_lover'], true)) {
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
                    'Your artist account was created. Please complete your membership application.'
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

        if (! in_array($role, ['admin', 'artist', 'art_lover'], true)) {
            return redirect()->route('auth.choose-role');
        }

        return view('auth.login', compact('role'));
    }

    /**
     * Authenticate the user and verify the selected account type.
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
        if (! $user || $user->role !== $validated['role']) {
            return back()
                ->withErrors([
                    'email' => 'The email, password, or selected account type is incorrect.',
                ])
                ->withInput(
                    $request->only('email', 'role', 'remember')
                );
        }

        // Verify the password.
        if (! Auth::attempt([
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

        // Artists must complete membership approval and payment verification.
        if ($user->role === 'artist') {
            $application = $user->artistApplication;

            if (
                ! $application ||
                $application->status !== 'approved' ||
                $application->payment_status !== 'verified'
            ) {
                return redirect()
                    ->route('artist.application')
                    ->with(
                        'success',
                        'You are signed in, but your artist membership must be approved and your payment verified before you can upload artwork.'
                    );
            }

            // Approved artists land on Discover after login.
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

        if (! $user || $user->role !== 'artist') {
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

        if (! $user || $user->role !== 'artist') {
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

        // Save the application.
        $application = $user->artistApplication()->create([
            'artist_name' => $validated['artist_name'],
            'biography' => $validated['biography'],

            // Retain compatibility with the existing column.
            'art_category' => implode(
                ', ',
                $validated['art_categories']
            ),

            // Save all selected categories.
            'art_categories' => $validated['art_categories'],

            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'membership_fee' => $fee,
            'payment_method' => 'manual',
        ]);

        // Update the artist's membership status.
        $user->membership_status = 'pending';
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
                'Your application has been submitted. You can now submit your manual payment transaction ID.'
            );
    }

    /**
     * Submit manual membership payment details.
     * Notify every admin after the payment reference is saved.
     */
    public function submitMembershipPayment(Request $request)
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'artist') {
            abort(403, 'Only artist accounts can submit payment details.');
        }

        $application = $user->artistApplication;

        if (! $application) {
            return redirect()
                ->route('artist.application')
                ->with(
                    'error',
                    'Please submit your membership application first.'
                );
        }

        if ($application->payment_status === 'verified') {
            return redirect()
                ->route('artist.application')
                ->with(
                    'success',
                    'Your payment has already been verified.'
                );
        }

        $validated = $request->validate([
            'payment_reference' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $application->payment_reference = trim(
            $validated['payment_reference']
        );
        $application->payment_method = 'manual';
        $application->payment_status = 'pending';
        $application->payment_submitted_at = now();
        $application->payment_verified_at = null;
        $application->save();

        // Notify all admins that the artist submitted payment details.
        User::where('role', 'admin')->get()->each(
            function (User $admin) use ($user, $application) {
                $admin->notify(new ArtfyDatabaseNotification(
                    'artfy.membership_payment_submitted',
                    [
                        'title' => 'Membership payment needs verification',
                        'message' => $user->name
                            . ' has submitted a membership payment reference for verification.',
                        'url' => route(
                            'admin.memberships.index',
                            [],
                            false
                        ),
                        'artist_id' => $user->id,
                        'artist_name' => $application->artist_name,
                        'application_id' => $application->id,
                        'payment_status' => $application->payment_status,
                    ]
                ));
            }
        );

        return redirect()
            ->route('artist.application')
            ->with(
                'success',
                'Your transaction ID has been submitted for Admin verification.'
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
