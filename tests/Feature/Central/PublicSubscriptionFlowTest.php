<?php

use App\Models\SuperAdmin;
use App\Models\University;
use Illuminate\Support\Str;

test('public user can view landing and pricing pages', function () {
    $this->get(route('public.landing'))->assertOk();
    $this->get(route('public.pricing'))->assertOk();
});

test('public pricing signup creates pending tenant and subscription', function () {
    $tenantId = 'public-signup-'.Str::lower(Str::random(6));

    $response = $this->post(route('public.subscribe'), [
        'name' => 'Public Signup University',
        'school_address' => 'Public District',
        'tenant_admin_name' => 'Public Admin',
        'tenant_admin_email' => 'public.admin@example.test',
        'subdomain' => $tenantId,
        'plan' => 'basic',
        'billing_cycle' => 'monthly',
    ]);

    $response->assertRedirect(route('public.pricing'));

    $this->assertDatabaseHas('tenants', [
        'id' => $tenantId,
        'status' => 'pending',
        'plan' => 'basic',
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $tenantId,
        'status' => 'pending',
        'plan' => 'basic',
    ]);

    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenantId,
    ]);

    $university = University::query()->findOrFail($tenantId);
    expect($university->getInternal('create_database'))->toBeFalse();
    $databaseName = (string) $university->database()->getName();

    expect($databaseName)->toStartWith((string) config('tenancy.database.prefix'));
    expect($databaseName)->not->toContain($tenantId);
});

test('approving a public pending tenant enables database provisioning', function () {
    $tenantId = 'public-approve-'.Str::lower(Str::random(6));

    $this->post(route('public.subscribe'), [
        'name' => 'Public Pending University',
        'school_address' => 'Pending District',
        'tenant_admin_name' => 'Pending Admin',
        'tenant_admin_email' => 'pending.admin@example.test',
        'subdomain' => $tenantId,
        'plan' => 'basic',
        'billing_cycle' => 'monthly',
    ])->assertRedirect(route('public.pricing'));

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'public-approve-super-admin@example.test',
        'password' => 'password',
    ]);

    $university = University::query()->findOrFail($tenantId);

    $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('central.universities.approve', $university))
        ->assertRedirect(route('central.universities.index'));

    $university->refresh();

    expect($university->status)->toBe('active');
    expect($university->subscription?->status)->toBe('active');
    expect($university->getInternal('create_database'))->toBeTrue();
});
