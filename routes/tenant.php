<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ForcePasswordController;
use App\Http\Controllers\Tenant\GameController;
use App\Http\Controllers\Tenant\PlayerController;
use App\Http\Controllers\Tenant\ProFeatureController;
use App\Http\Controllers\Tenant\SportController;
use App\Http\Controllers\Tenant\StandingsController;
use App\Http\Controllers\Tenant\TeamController;
use App\Http\Controllers\Tenant\TenantAdminInviteController;
use App\Http\Controllers\Tenant\VenueController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'tenant.subscription',
])->group(function () {
    Route::get('/app', function () {
        return redirect()->route('tenant.dashboard');
    });

    Route::middleware('guest')->group(function () {
        Route::get('/app/login', [AuthenticatedSessionController::class, 'create'])->name('tenant.login');
        Route::post('/app/login', [AuthenticatedSessionController::class, 'store'])->name('tenant.login.store');

        Route::get('/app/admin-invite/{token}', [TenantAdminInviteController::class, 'edit'])->name('tenant.admin-invite.edit');
        Route::post('/app/admin-invite', [TenantAdminInviteController::class, 'update'])->name('tenant.admin-invite.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/app/logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

        Route::get('/app/force-password', [ForcePasswordController::class, 'edit'])->name('tenant.force-password.edit');
        Route::put('/app/force-password', [ForcePasswordController::class, 'update'])->name('tenant.force-password.update');
    });

    Route::middleware(['auth', 'tenant.password.updated', 'verified'])->group(function () {
        Route::get('/app/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/app/standings', [StandingsController::class, 'index'])->name('tenant.standings.index');

        Route::middleware('check.role:university_admin,sports_facilitator')->prefix('/app')->name('tenant.')->group(function () {
            Route::resource('sports', SportController::class)->except(['show']);
            Route::resource('venues', VenueController::class)->except(['show']);
            Route::resource('teams', TeamController::class)->except(['show']);
            Route::resource('players', PlayerController::class)->except(['show']);
            Route::resource('games', GameController::class)->except(['show']);
            Route::get('audits/game-results', [GameController::class, 'auditsIndex'])->name('audits.game-results.index');
            Route::patch('games/{game}/result', [GameController::class, 'submitResult'])->name('games.result');
            Route::get('games/{game}/audits', [GameController::class, 'auditTrail'])->name('games.audits');

            Route::middleware('check.plan:pro')->prefix('pro')->name('pro.')->group(function () {
                Route::get('analytics', [ProFeatureController::class, 'analytics'])->name('analytics');
                Route::get('bracket', [ProFeatureController::class, 'bracket'])->name('bracket');
                Route::get('bracket/audits', [ProFeatureController::class, 'bracketAudits'])->name('bracket.audits');
                Route::post('bracket/generate', [ProFeatureController::class, 'generateBracket'])->name('bracket.generate');
                Route::patch('bracket/matches/{match}/winner', [ProFeatureController::class, 'storeBracketResult'])->name('bracket.matches.winner');
                Route::get('exports/standings.csv', [ProFeatureController::class, 'exportStandingsCsv'])->name('exports.standings.csv');
                Route::get('exports/standings.pdf', [ProFeatureController::class, 'exportStandingsPdf'])->name('exports.standings.pdf');
                Route::get('exports/result-audits.csv', [ProFeatureController::class, 'exportResultAuditsCsv'])->name('exports.result-audits.csv');
                Route::get('exports/result-audits.pdf', [ProFeatureController::class, 'exportResultAuditsPdf'])->name('exports.result-audits.pdf');
            });
        });

        Route::get('/app/facilitator', function () {
            return 'Sports facilitator dashboard';
        })->middleware('check.role:sports_facilitator,university_admin');
    });
});
