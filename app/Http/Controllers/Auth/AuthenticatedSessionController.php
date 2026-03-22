<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stancl\Tenancy\Database\Models\Domain;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (tenant() !== null) {
            return view('tenant.auth.login');
        }

        $centralDomains = collect(config('tenancy.central_domains', []))
            ->map(fn ($domain): string => trim((string) $domain))
            ->filter()
            ->values();

        if ($centralDomains->contains($request->getHost())) {
            return redirect()->route('central.login');
        }

        $isTenantDomain = Domain::query()
            ->where('domain', $request->getHost())
            ->exists();

        if ($isTenantDomain) {
            return redirect('/app/login');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (tenant() !== null && (bool) $request->user()?->must_change_password) {
            return redirect()->route('tenant.force-password.edit');
        }

        $defaultRoute = tenant() !== null ? 'tenant.dashboard' : 'dashboard';

        return redirect()->intended(route($defaultRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
