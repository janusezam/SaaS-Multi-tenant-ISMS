<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSuperAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('super_admin')->check()) {
            return redirect()->route('central.login');
        }

        $request->setUserResolver(static fn () => Auth::guard('super_admin')->user());

        return $next($request);
    }
}
