<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantAdminInviteController extends Controller
{
    public function edit(Request $request, string $token): View
    {
        $email = (string) $request->query('email', '');

        if ($this->resolveInvitee($email, $token, ['university_admin']) === null) {
            abort(404);
        }

        return view('tenant.auth.accept-invite', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $this->resolveInvitee((string) $validated['email'], (string) $validated['token'], ['university_admin']);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'This invite link is invalid or expired.',
            ]);
        }

        $attributes = [
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $attributes['must_change_password'] = false;
        }

        if (Schema::hasColumn('users', 'invite_token_hash')) {
            $attributes['invite_token_hash'] = null;
        }

        if (Schema::hasColumn('users', 'invite_expires_at')) {
            $attributes['invite_expires_at'] = null;
        }

        if (Schema::hasColumn('users', 'invite_sent_at')) {
            $attributes['invite_sent_at'] = null;
        }

        if (Schema::hasColumn('users', 'email_verified_at') && $user->email_verified_at === null) {
            $attributes['email_verified_at'] = now();
        }

        $user->forceFill($attributes)->save();

        return redirect()->route('tenant.login')->with('status', 'Your password has been set. Please sign in.');
    }

    public function showUserInvite(Request $request, string $token): View
    {
        $email = (string) $request->query('email', '');

        if ($this->resolveInvitee($email, $token, ['sports_facilitator', 'team_coach', 'student_player']) === null) {
            abort(404);
        }

        return view('tenant.auth.accept-user-invite', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function activateUserInvite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $user = $this->resolveInvitee((string) $validated['email'], (string) $validated['token'], ['sports_facilitator', 'team_coach', 'student_player']);

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'This invite link is invalid or expired.',
            ]);
        }

        $attributes = [];

        if (Schema::hasColumn('users', 'invite_token_hash')) {
            $attributes['invite_token_hash'] = null;
        }

        if (Schema::hasColumn('users', 'invite_expires_at')) {
            $attributes['invite_expires_at'] = null;
        }

        if (Schema::hasColumn('users', 'invite_sent_at')) {
            $attributes['invite_sent_at'] = null;
        }

        if (Schema::hasColumn('users', 'email_verified_at') && $user->email_verified_at === null) {
            $attributes['email_verified_at'] = now();
        }

        $user->forceFill($attributes)->save();

        return redirect()->route('tenant.login')->with('status', 'Your tenant account is now active. Please sign in.');
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function resolveInvitee(string $email, string $token, array $roles): ?User
    {
        if ($email === '' || ! Schema::hasColumn('users', 'invite_token_hash') || ! Schema::hasColumn('users', 'invite_expires_at')) {
            return null;
        }

        $user = User::query()
            ->whereIn('role', $roles)
            ->where('email', $email)
            ->first();

        if ($user === null || ! is_string($user->invite_token_hash) || $user->invite_token_hash === '' || $user->invite_expires_at === null || $user->invite_expires_at->isPast()) {
            return null;
        }

        return hash_equals($user->invite_token_hash, hash('sha256', $token)) ? $user : null;
    }
}
