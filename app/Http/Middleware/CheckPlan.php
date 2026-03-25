<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        if ($plans === []) {
            return $next($request);
        }

        $tenant = tenant();

        if ($tenant === null) {
            abort(Response::HTTP_FORBIDDEN, 'Tenant context is required.');
        }

        if (! in_array($tenant->currentPlan(), $plans, true)) {
            return redirect()
                ->route('tenant.dashboard')
                ->with('upgrade_notice', 'Upgrade to Pro to access this feature.');
        }

        return $next($request);
    }
}
