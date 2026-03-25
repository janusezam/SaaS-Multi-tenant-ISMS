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

        $dueDate = $tenant->currentDueDate();
        $isExpired = $dueDate !== null && $dueDate->copy()->endOfDay()->isPast();
        $currentStatus = $tenant->currentStatus();

        if ($currentStatus !== 'active' || $isExpired) {
            abort(Response::HTTP_LOCKED, 'School subscription is not active yet. Please contact your school administrator.');
        }

        return $next($request);
    }
}
