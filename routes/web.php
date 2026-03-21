<?php

use App\Http\Controllers\Central\UniversityController;
use App\Http\Controllers\Central\SubscriptionNotificationLogController;
use App\Http\Controllers\Central\Auth\AuthenticatedSessionController as CentralAuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

        Route::resource('universities', UniversityController::class)->except(['show']);

        Route::patch('universities/{university}/suspend', [UniversityController::class, 'suspend'])
            ->name('universities.suspend');

        Route::patch('universities/{university}/reactivate', [UniversityController::class, 'reactivate'])
            ->name('universities.reactivate');

        Route::patch('universities/{university}/extend', [UniversityController::class, 'extend'])
            ->name('universities.extend');
    });
});

require __DIR__.'/auth.php';
