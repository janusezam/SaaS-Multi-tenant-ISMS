<?php

use App\Models\User;
use App\Models\University;
use Illuminate\Support\Facades\Route;

test('coach access to schedules does not redirect', function () {
    // Create a tenant first
    $tenant = University::query()->create([
        'id' => 'test-tenant',
        'name' => 'Test Tenant',
        'status' => 'active',
        'plan' => 'pro', // Assuming a plan that has these features
    ]);
    $tenant->domains()->create(['domain' => 'test.localhost']);

    $user = User::factory()->coach()->create([
        'must_change_password' => false,
        'email_verified_at' => now(),
    ]);

    // We need to initialize tenancy for the test
    tenancy()->initialize($tenant);

    $response = $this->actingAs($user)
        ->get('/app/coach/schedules');

    // Check if it's a redirect
    if ($response->isRedirect()) {
        $this->fail('Redirected to: ' . $response->headers->get('Location'));
    }

    $response->assertOk();
});

test('player access to schedule does not redirect', function () {
    $tenant = University::query()->first() ?? University::query()->create([
        'id' => 'test-tenant-2',
        'name' => 'Test Tenant 2',
        'status' => 'active',
        'plan' => 'pro',
    ]);
    
    $user = User::factory()->player()->create([
        'must_change_password' => false,
        'email_verified_at' => now(),
    ]);

    tenancy()->initialize($tenant);

    $response = $this->actingAs($user)
        ->get('/app/player/my-schedule');

    if ($response->isRedirect()) {
        $this->fail('Redirected to: ' . $response->headers->get('Location'));
    }

    $response->assertOk();
});
