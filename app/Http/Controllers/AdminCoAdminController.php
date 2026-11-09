<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminCoAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.co-admins.index', [
            'coAdmins' => User::query()
                ->where('role', 'co_admin')
                ->orderBy('created_at')
                ->get(),
            'coAdminLimit' => 3,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
        ]);

        $created = DB::transaction(function () use ($request, $validated): bool {
            User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->firstOrFail();

            if (User::query()->where('role', 'co_admin')->count() >= 3) {
                return false;
            }

            $coAdmin = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $coAdmin->role = 'co_admin';
            $coAdmin->membership_status = 'not_applicable';
            $coAdmin->save();

            return true;
        });

        if (! $created) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'The exhibition already has the maximum of three co-admin accounts.',
                ]);
        }

        return redirect()->route('admin.co-admins.index')
            ->with('success', 'Co-admin account created.');
    }

    public function update(Request $request, User $coAdmin): RedirectResponse
    {
        abort_unless($coAdmin->role === 'co_admin', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($coAdmin->id),
            ],
            'password' => ['nullable', 'confirmed', 'string', 'min:8'],
        ]);

        $coAdmin->name = $validated['name'];
        $coAdmin->email = $validated['email'];
        if ($validated['password'] !== null && $validated['password'] !== '') {
            $coAdmin->password = $validated['password'];
        }
        $coAdmin->save();

        return redirect()->route('admin.co-admins.index')
            ->with('success', 'Co-admin account updated.');
    }
}
