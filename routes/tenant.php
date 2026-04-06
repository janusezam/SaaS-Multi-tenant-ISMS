<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\CoachTeamController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ForcePasswordController;
use App\Http\Controllers\Tenant\GameController;
use App\Http\Controllers\Tenant\PlayerController;
use App\Http\Controllers\Tenant\PlayerEngagementController;
use App\Http\Controllers\Tenant\ProFeatureController;
use App\Http\Controllers\Tenant\SportController;
use App\Http\Controllers\Tenant\StandingsController;
use App\Http\Controllers\Tenant\SubscriptionController;
use App\Http\Controllers\Tenant\TeamController;
use App\Http\Controllers\Tenant\TenantAdminInviteController;
use App\Http\Controllers\Tenant\TenantRbacController;
use App\Http\Controllers\Tenant\TenantUserController;
use App\Http\Controllers\Tenant\VenueController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Symfony\Component\HttpFoundation\Response;

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
    'tenant.runtime.metrics',
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
        Route::get('/app/subscription', [SubscriptionController::class, 'show'])->name('tenant.subscription.show');
        Route::get('/app/subscription/pricing-preview', [SubscriptionController::class, 'preview'])->name('tenant.subscription.preview');
        Route::post('/app/subscription/upgrade-requests', [SubscriptionController::class, 'submit'])->name('tenant.subscription.upgrade-requests.store');

        Route::redirect('/app/oach/schdules', '/app/coach/schedules');
        Route::redirect('/app/oach/my-team', '/app/coach/my-team');
        Route::redirect('/app/player/my-shcedule', '/app/player/my-schedule');

        Route::middleware('check.role:university_admin')->prefix('/app')->name('tenant.')->group(function () {
            Route::resource('users', TenantUserController::class)->except(['show']);
            Route::get('rbac', [TenantRbacController::class, 'index'])->name('rbac.index');
            Route::put('rbac', [TenantRbacController::class, 'update'])->name('rbac.update');
            Route::resource('sports', SportController::class)->except(['show']);
            Route::resource('teams', TeamController::class)->except(['show']);
            Route::resource('players', PlayerController::class)->except(['show']);

            Route::prefix('pro')->name('pro.')->group(function () {
                Route::get('analytics', [ProFeatureController::class, 'analytics'])->name('analytics');
                Route::get('bracket', [ProFeatureController::class, 'bracket'])->name('bracket');

                Route::middleware('check.feature:bracket')->group(function () {
                    Route::get('bracket/audits', [ProFeatureController::class, 'bracketAudits'])->name('bracket.audits');
                    Route::post('bracket/generate', [ProFeatureController::class, 'generateBracket'])->name('bracket.generate');
                    Route::patch('bracket/matches/{match}/winner', [ProFeatureController::class, 'storeBracketResult'])->name('bracket.matches.winner');
                    Route::get('exports/standings.csv', [ProFeatureController::class, 'exportStandingsCsv'])->name('exports.standings.csv');
                    Route::get('exports/standings.pdf', [ProFeatureController::class, 'exportStandingsPdf'])->name('exports.standings.pdf');
                    Route::get('exports/result-audits.csv', [ProFeatureController::class, 'exportResultAuditsCsv'])->name('exports.result-audits.csv');
                    Route::get('exports/result-audits.pdf', [ProFeatureController::class, 'exportResultAuditsPdf'])->name('exports.result-audits.pdf');
                });
            });
        });

        Route::middleware('check.role:university_admin,sports_facilitator')->prefix('/app')->name('tenant.')->group(function () {
            Route::resource('venues', VenueController::class)->except(['show'])->middleware('check.permission:facilitator.venues.manage');
            Route::resource('games', GameController::class)->except(['show'])->middleware('check.permission:facilitator.games.manage');
            Route::get('audits/game-results', [GameController::class, 'auditsIndex'])->name('audits.game-results.index')->middleware('check.permission:facilitator.results.audit');
            Route::patch('games/{game}/result', [GameController::class, 'submitResult'])->name('games.result')->middleware('check.permission:facilitator.results.submit');
            Route::get('games/{game}/audits', [GameController::class, 'auditTrail'])->name('games.audits')->middleware('check.permission:facilitator.results.audit');
        });

        Route::redirect('/app/coach/shedules', '/app/coach/schedules');
        Route::redirect('/app/coach/my-teams', '/app/coach/my-team');
        Route::redirect('/app/player/my-schedules', '/app/player/my-schedule');
        Route::redirect('/app/player/my-shedule', '/app/player/my-schedule');

        Route::get('/app/coach/schedules', function () {
            abort_unless(auth()->user()?->hasTenantRole('team_coach') === true, Response::HTTP_FORBIDDEN);

            return view('tenant.coach.schedules');
        })->middleware('check.permission:coach.schedules.view')->name('tenant.coach.schedules');

        Route::middleware('check.role:team_coach')->prefix('/app/coach')->name('tenant.coach.')->group(function () {
            Route::post('/games/{game}/lineup', [CoachTeamController::class, 'updateLineup'])->middleware('check.permission:coach.lineup.manage')->name('games.lineup.update');
            Route::post('/announcements', [CoachTeamController::class, 'storeAnnouncement'])->middleware('check.permission:coach.announcements.manage')->name('announcements.store');
        });

        Route::get('/app/coach/my-team', function () {
            abort_unless(auth()->user()?->hasTenantRole('team_coach') === true, Response::HTTP_FORBIDDEN);

            return view('tenant.coach.my-team');
        })->middleware('check.permission:coach.team.view')->name('tenant.coach.my-team');

        Route::get('/app/player/my-schedule', function () {
            abort_unless(auth()->user()?->hasTenantRole('student_player') === true, Response::HTTP_FORBIDDEN);

            return view('tenant.player.my-schedule');
        })->middleware('check.permission:player.schedule.view')->name('tenant.player.my-schedule');

        Route::middleware('check.role:student_player')->prefix('/app/player')->name('tenant.player.')->group(function () {
            Route::patch('/assignments/{assignment}/attendance', [PlayerEngagementController::class, 'updateAttendance'])->middleware('check.permission:player.attendance.respond')->name('assignments.attendance.update');
        });

        Route::get('/app/facilitator', function () {
            return 'Sports facilitator dashboard';
        })->middleware('check.role:sports_facilitator,university_admin');
    });
});
