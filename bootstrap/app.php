<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPlan;
use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.role' => CheckRole::class,
            'check.plan' => CheckPlan::class,
            'auth.super_admin' => AuthenticateSuperAdmin::class,
            'tenant.subscription' => EnsureTenantSubscriptionIsActive::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->routeIs('central.*')) {
                return route('central.login');
            }

            if (tenant() !== null) {
                return route('tenant.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
