<?php

use App\Http\Middleware\AuthenticateSuperAdmin;
use App\Http\Middleware\CheckPlan;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureTenantPasswordIsUpdated;
use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            'tenant.password.updated' => EnsureTenantPasswordIsUpdated::class,
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

        $middleware->redirectUsersTo(function (Request $request): string {
            if (Auth::guard('super_admin')->check()) {
                return route('central.universities.index');
            }

            if (tenant() !== null) {
                return route('tenant.dashboard');
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            $tenant = tenant();

            if ($exception->getStatusCode() !== SymfonyResponse::HTTP_LOCKED || $tenant === null) {
                return null;
            }

            $currentStatus = method_exists($tenant, 'currentStatus')
                ? (string) $tenant->currentStatus()
                : (string) ($tenant->status ?? 'inactive');

            $currentDueDate = method_exists($tenant, 'currentDueDate')
                ? $tenant->currentDueDate()
                : ($tenant->expires_at ?? null);

            return response()->view('errors.tenant-locked', [
                'message' => $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'School subscription is not active yet. Please contact your school administrator.',
                'tenantName' => (string) ($tenant->name ?? 'Your school'),
                'tenantStatus' => $currentStatus,
                'tenantDueDate' => $currentDueDate,
            ], SymfonyResponse::HTTP_LOCKED);
        });
    })->create();
