<?php

namespace App\Http\Controllers;

use App\Mail\PasswordRecoveryCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class PasswordRecoveryController extends Controller
{
    private const CODE_LIFETIME_MINUTES = 10;

    private const CODE_RESEND_SECONDS = 60;

    private const MAX_CODE_ATTEMPTS = 5;

    public function requestForm(): View
    {
        return view('auth.password-recovery-request');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower(trim($validated['email']));
        $request->session()->put('password_recovery_email', $email);

        $mailPassword = (string) config('mail.mailers.smtp.password', '');
        $mailFrom = (string) config('mail.from.address', '');
        $mailIsConfigured = config('mail.default') === 'smtp'
            && $mailPassword !== ''
            && ! str_contains($mailPassword, 'PASTE_')
            && $mailFrom !== ''
            && ! str_contains($mailFrom, 'replace-with');

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereIn('role', ['artist', 'art_lover'])
            ->first();

        if ($user && $mailIsConfigured) {
            $existing = DB::table('password_recovery_codes')
                ->where('email', $email)
                ->first();

            if (
                ! $existing
                || ! now()->subSeconds(self::CODE_RESEND_SECONDS)
                    ->lt($existing->created_at)
            ) {
                $code = (string) random_int(100000, 999999);
                $codeHash = Hash::make($code);
                $now = now();

                DB::table('password_recovery_codes')->updateOrInsert(
                    ['email' => $email],
                    [
                        'user_id' => $user->id,
                        'code_hash' => $codeHash,
                        'expires_at' => $now->copy()
                            ->addMinutes(self::CODE_LIFETIME_MINUTES),
                        'attempts' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                try {
                    Mail::to($user->email)->send(
                        new PasswordRecoveryCodeMail(
                            $user->name,
                            $code,
                            self::CODE_LIFETIME_MINUTES
                        )
                    );
                } catch (Throwable $exception) {
                    report($exception);
                    DB::table('password_recovery_codes')
                        ->where('email', $email)
                        ->where('code_hash', $codeHash)
                        ->delete();

                    return redirect()
                        ->route('password.request')
                        ->withInput(['email' => $email])
                        ->withErrors([
                            'email' => 'Artfy could not send the recovery email. The mail provider rejected the SMTP login or sender address. Check the mail settings and try again.',
                        ]);
                }
            }
        }

        if ($user && ! $mailIsConfigured) {
            return redirect()
                ->route('password.request')
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Recovery email is not configured yet. Add your Brevo SMTP key and a sender address verified in Brevo, then try again.',
                ]);
        }

        $status = config('mail.default') === 'log'
            ? 'Email delivery is in log-only mode. In local development, the recovery email is written to storage/logs/laravel.log. Configure SMTP or another mail provider to deliver codes to inboxes.'
            : 'If this email belongs to an Artist or Art Lover account, a recovery code has been sent. Check your inbox and spam folder.';

        return redirect()
            ->route('password.code')
            ->with('status', $status);
    }

    public function codeForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_recovery_email');

        if (! is_string($email) || $email === '') {
            return redirect()->route('password.request');
        }

        return view('auth.password-recovery-code', compact('email'));
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $email = $request->session()->get('password_recovery_email');

        if (! is_string($email) || $email === '') {
            return redirect()->route('password.request');
        }

        $recovery = DB::table('password_recovery_codes')
            ->where('email', $email)
            ->first();

        if (
            ! $recovery
            || now()->greaterThan($recovery->expires_at)
            || $recovery->attempts >= self::MAX_CODE_ATTEMPTS
        ) {
            DB::table('password_recovery_codes')->where('email', $email)->delete();

            return back()->withErrors([
                'code' => 'That code is invalid or expired. Request a new code and try again.',
            ]);
        }

        $user = User::query()
            ->whereKey($recovery->user_id)
            ->whereIn('role', ['artist', 'art_lover'])
            ->first();

        if (! $user || ! Hash::check($validated['code'], $recovery->code_hash)) {
            DB::table('password_recovery_codes')
                ->where('email', $email)
                ->increment('attempts');

            return back()->withErrors([
                'code' => 'That code is invalid or expired. Request a new code and try again.',
            ]);
        }

        DB::table('password_recovery_codes')->where('email', $email)->delete();
        $request->session()->forget('password_recovery_email');
        $request->session()->put('password_recovery_user_id', $user->id);
        $request->session()->put(
            'password_recovery_verified_until',
            now()->addMinutes(5)->timestamp
        );

        return redirect()->route('password.reset');
    }

    public function resetForm(Request $request): View|RedirectResponse
    {
        if (! $this->verifiedRecoveryUser($request)) {
            $this->clearRecoveryGrant($request);

            return redirect()->route('password.request')->withErrors([
                'email' => 'Request and verify a recovery code before choosing a new password.',
            ]);
        }

        return view('auth.password-recovery-reset');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $user = $this->verifiedRecoveryUser($request);

        if (! $user) {
            $this->clearRecoveryGrant($request);

            return redirect()->route('password.request')->withErrors([
                'email' => 'Your recovery session expired. Request a new code.',
            ]);
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->remember_token = Str::random(60);
        $user->save();

        $this->clearRecoveryGrant($request);
        $request->session()->regenerate();

        return redirect()->route('login', [
            'role' => $user->role,
        ])->with('success', 'Your password has been changed. You can now sign in.');
    }

    private function verifiedRecoveryUser(Request $request): ?User
    {
        $expiresAt = $request->session()->get('password_recovery_verified_until');
        $userId = $request->session()->get('password_recovery_user_id');

        if (! is_numeric($expiresAt) || now()->timestamp > (int) $expiresAt) {
            return null;
        }

        return User::query()
            ->whereKey($userId)
            ->whereIn('role', ['artist', 'art_lover'])
            ->first();
    }

    private function clearRecoveryGrant(Request $request): void
    {
        $request->session()->forget([
            'password_recovery_user_id',
            'password_recovery_verified_until',
            'password_recovery_email',
        ]);
    }
}
