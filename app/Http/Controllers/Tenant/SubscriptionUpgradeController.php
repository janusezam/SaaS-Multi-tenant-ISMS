<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class SubscriptionUpgradeController extends Controller
{
    public function requestProUpgrade(): RedirectResponse
    {
        $tenant = tenant();
        $user = auth()->user();

        if ($tenant === null || $user === null) {
            return redirect()->route('tenant.dashboard');
        }

        $relativeSignedUrl = URL::temporarySignedRoute(
            'central.upgrade.requests.store',
            now()->addMinutes(20),
            [
                'tenant' => $tenant->id,
                'plan' => 'pro',
                'email' => $user->email,
            ],
            absolute: false,
        );

        return redirect()->away($this->centralBaseUrl().$relativeSignedUrl);
    }

    private function centralBaseUrl(): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http';
        $centralDomains = config('tenancy.central_domains', ['localhost']);

        foreach ($centralDomains as $domain) {
            if (is_string($domain) && ! filter_var($domain, FILTER_VALIDATE_IP)) {
                return sprintf('%s://%s', $scheme, trim($domain));
            }
        }

        return sprintf('%s://localhost', $scheme);
    }
}
