<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $isAuthorized = collect($roles)->contains(fn (string $role): bool => $user->hasTenantRole($role));

        if (! $isAuthorized) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
