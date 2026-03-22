<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPasswordIsUpdated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() === null || ! $request->user()) {
            return $next($request);
        }

        if (! $request->user()->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('tenant.force-password.*') || $request->routeIs('tenant.logout')) {
            return $next($request);
        }

        return redirect()->route('tenant.force-password.edit');
    }
}
