<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant') ?? tenant();

        if ($tenant === null) {
            return $next($request);
        }

        $isExpired = $tenant->expires_at !== null && $tenant->expires_at->copy()->endOfDay()->isPast();

        if ($tenant->status !== 'active' || $isExpired) {
            abort(Response::HTTP_LOCKED, 'School subscription is suspended or expired.');
        }

        return $next($request);
    }
}
