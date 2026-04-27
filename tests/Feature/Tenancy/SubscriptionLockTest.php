<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\University;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('active tenant can continue request flow', function () {
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'uni-active',
        'name' => 'Active University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(7),
    ]));

    $request = Request::create('/dashboard', 'GET');
    $request->attributes->set('tenant', $tenant);

    $response = (new EnsureTenantSubscriptionIsActive)->handle(
        $request,
        fn () => response('ok', 200)
    );

    expect($response->getStatusCode())->toBe(200);
});

test('suspended tenant is locked', function () {
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'uni-suspended',
        'name' => 'Suspended University',
        'plan' => 'pro',
        'status' => 'suspended',
        'expires_at' => now()->addDays(7),
    ]));

    $request = Request::create('/dashboard', 'GET');
    $request->attributes->set('tenant', $tenant);

    try {
        (new EnsureTenantSubscriptionIsActive)->handle(
            $request,
            fn () => response('ok', 200)
        );

        $this->fail('Expected tenant subscription middleware to throw a lock exception.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(423);
    }
});

test('expired tenant is locked', function () {
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'uni-expired',
        'name' => 'Expired University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->subDay(),
    ]));

    $request = Request::create('/dashboard', 'GET');
    $request->attributes->set('tenant', $tenant);

    try {
        (new EnsureTenantSubscriptionIsActive)->handle(
            $request,
            fn () => response('ok', 200)
        );

        $this->fail('Expected tenant subscription middleware to throw a lock exception.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBe(423);
    }
});
