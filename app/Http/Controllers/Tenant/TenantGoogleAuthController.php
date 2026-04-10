<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class TenantGoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return redirect()->route('tenant.login')->with('status', 'Google sign-in is not configured yet.');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return redirect()->route('tenant.login')->with('status', 'Google sign-in is not configured yet.');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable) {
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'Google sign-in failed. Please try again.',
            ]);
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();

        if ($email === '' || $googleId === '') {
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'Google account email is required for sign-in.',
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'Your account is not provisioned for this tenant yet.',
            ]);
        }

        if (filled($user->google_id) && $user->google_id !== $googleId) {
            return redirect()->route('tenant.login')->withErrors([
                'email' => 'This account is already linked to a different Google account.',
            ]);
        }

        $user->forceFill([
            'google_id' => $googleId,
            'google_email' => $email,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user, true);

        $request->session()->regenerate();

        if ((bool) $user->must_change_password) {
            return redirect()->route('tenant.force-password.edit');
        }

        return redirect()->intended(route('tenant.dashboard', absolute: false));
    }

    private function isGoogleConfigured(): bool
    {
        return filled((string) config('services.google.client_id'))
            && filled((string) config('services.google.client_secret'))
            && filled((string) config('services.google.redirect'));
    }
}
