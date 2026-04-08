<?php

use App\Http\Controllers\Central\Auth\AuthenticatedSessionController as CentralAuthenticatedSessionController;
use App\Http\Controllers\Central\BusinessControl\CouponController;
use App\Http\Controllers\Central\BusinessControl\DashboardController;
use App\Http\Controllers\Central\BusinessControl\PlanController;
use App\Http\Controllers\Central\BusinessControl\PromotionCampaignController;
use App\Http\Controllers\Central\BusinessControl\TenantSupportTicketController;
use App\Http\Controllers\Central\BusinessControl\UpgradeRequestController;
use App\Http\Controllers\Central\PublicSubscriptionController;
use App\Http\Controllers\Central\SubscriptionNotificationLogController;
use App\Http\Controllers\Central\TenantMonitoringController;
use App\Http\Controllers\Central\UniversityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Database\Models\Domain;

Route::get('/', function (Request $request) {
    $centralDomains = collect(config('tenancy.central_domains', []))
        ->map(fn ($domain): string => trim((string) $domain))
        ->filter()
        ->values();

    if (! $centralDomains->contains($request->getHost())
        && Domain::query()->where('domain', $request->getHost())->exists()) {
        return redirect('/app');
    }

    return redirect()->route('public.landing');
});

Route::get('/landing', [PublicSubscriptionController::class, 'landing'])->name('public.landing');

Route::get('/pricing', [PublicSubscriptionController::class, 'pricing'])->name('public.pricing');
Route::post('/subscribe', [PublicSubscriptionController::class, 'subscribe'])->name('public.subscribe');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('central')->name('central.')->group(function () {
    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login', [CentralAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [CentralAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth.super_admin')->group(function () {
        Route::post('/logout', [CentralAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('subscription-notification-logs', [SubscriptionNotificationLogController::class, 'index'])
            ->name('subscription-notification-logs.index');

        Route::get('tenant-monitoring', [TenantMonitoringController::class, 'index'])
            ->name('tenant-monitoring.index');
        Route::get('tenant-monitoring/data', [TenantMonitoringController::class, 'data'])
            ->name('tenant-monitoring.data');

        Route::resource('universities', UniversityController::class)->except(['show']);

        Route::prefix('business-control')->name('business-control.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');

            Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
            Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
            Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
            Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

            Route::get('coupons', [CouponController::class, 'index'])->name('coupons.index');
            Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
            Route::patch('coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
            Route::get('coupons/{coupon}/redemptions', [CouponController::class, 'redemptions'])->name('coupons.redemptions');

            Route::get('campaigns', [PromotionCampaignController::class, 'index'])->name('campaigns.index');
            Route::post('campaigns', [PromotionCampaignController::class, 'store'])->name('campaigns.store');
            Route::patch('campaigns/{campaign}', [PromotionCampaignController::class, 'update'])->name('campaigns.update');
            Route::post('campaigns/{campaign}/apply-renewals', [PromotionCampaignController::class, 'applyToRenewals'])->name('campaigns.apply-renewals');

            Route::get('upgrade-requests', [UpgradeRequestController::class, 'index'])->name('upgrade-requests.index');
            Route::patch('upgrade-requests/{upgradeRequest}/approve', [UpgradeRequestController::class, 'approve'])->name('upgrade-requests.approve');
            Route::patch('upgrade-requests/{upgradeRequest}/reject', [UpgradeRequestController::class, 'reject'])->name('upgrade-requests.reject');

            Route::get('support-updates', [TenantSupportTicketController::class, 'index'])->name('support-updates.index');
            Route::post('support-updates/updates', [TenantSupportTicketController::class, 'storeUpdate'])->name('support-updates.updates.store');
            Route::patch('support-updates/tickets/{ticket}', [TenantSupportTicketController::class, 'updateTicket'])->name('support-updates.tickets.update');
        });

        Route::patch('universities/{university}/suspend', [UniversityController::class, 'suspend'])
            ->name('universities.suspend');

        Route::patch('universities/{university}/reactivate', [UniversityController::class, 'reactivate'])
            ->name('universities.reactivate');

        Route::patch('universities/{university}/extend', [UniversityController::class, 'extend'])
            ->name('universities.extend');

        Route::patch('universities/{university}/approve', [UniversityController::class, 'approve'])
            ->name('universities.approve');
    });
});

require __DIR__.'/auth.php';
