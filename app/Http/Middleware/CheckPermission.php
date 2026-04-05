<?php

namespace App\Http\Middleware;

use App\Support\TenantPermissionMatrix;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $permissionMatrix = app(TenantPermissionMatrix::class);

        if (! $permissionMatrix->allows($user, $permission)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
