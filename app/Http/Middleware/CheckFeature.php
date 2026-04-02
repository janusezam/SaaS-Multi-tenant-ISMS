<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$features): Response
    {
        if ($features === []) {
            return $next($request);
        }

        $tenant = tenant();

        if ($tenant === null) {
            abort(Response::HTTP_FORBIDDEN, 'Tenant context is required.');
        }

        foreach ($features as $feature) {
            if ($feature !== '' && method_exists($tenant, 'hasFeature') && $tenant->hasFeature($feature)) {
                return $next($request);
            }
        }

        return redirect()
            ->route('tenant.dashboard')
            ->with('upgrade_notice', 'Upgrade your subscription to unlock this feature.');
    }
}
