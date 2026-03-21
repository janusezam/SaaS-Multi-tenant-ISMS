<?php

use App\Models\University;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

test('authenticated super admin can view university index page', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.universities.index'));

    $response->assertOk();
    $response->assertSee('University Management');
});

test('authenticated super admin can create a university tenant', function () {
    Event::fake();

    $expectedBaseDomain = collect(config('tenancy.central_domains', ['localhost']))
        ->first(fn ($domain) => is_string($domain) && ! filter_var($domain, FILTER_VALIDATE_IP), 'localhost');

    $expectedBaseDomain = Str::startsWith($expectedBaseDomain, 'central.')
        ? Str::after($expectedBaseDomain, 'central.')
        : $expectedBaseDomain;

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-create@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->post(route('central.universities.store'), [
        'name' => 'Northern State University',
        'school_address' => 'North Avenue, City',
        'tenant_admin_name' => 'Nora Admin',
        'tenant_admin_email' => 'nora@nsu.test',
        'subdomain' => 'nsu',
        'plan' => 'pro',
        'subscription_starts_at' => now()->toDateString(),
        'expires_at' => now()->addDays(30)->toDateString(),
    ]);

    $response->assertRedirect(route('central.universities.index'));

    $this->assertDatabaseHas('tenants', [
        'id' => 'nsu',
        'name' => 'Northern State University',
        'school_address' => 'North Avenue, City',
        'tenant_admin_name' => 'Nora Admin',
        'tenant_admin_email' => 'nora@nsu.test',
        'plan' => 'pro',
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('domains', [
        'tenant_id' => 'nsu',
        'domain' => 'nsu.'.$expectedBaseDomain,
    ]);
});

test('tenant domain generation uses configured central base domain', function () {
    Event::fake();

    config()->set('tenancy.central_domains', ['127.0.0.1', 'isms.local']);

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-domain@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->post(route('central.universities.store'), [
        'name' => 'Metro University',
        'school_address' => 'Metro District',
        'tenant_admin_name' => 'Metro Admin',
        'tenant_admin_email' => 'admin@metro.test',
        'subdomain' => 'metro',
        'plan' => 'basic',
        'subscription_starts_at' => now()->toDateString(),
        'expires_at' => now()->addDays(30)->toDateString(),
    ]);

    $response->assertRedirect(route('central.universities.index'));

    $this->assertDatabaseHas('domains', [
        'tenant_id' => 'metro',
        'domain' => 'metro.isms.local',
    ]);
});

test('authenticated super admin can suspend and reactivate a university', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-suspend@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'east-university',
        'name' => 'East University',
        'school_address' => 'East District',
        'tenant_admin_name' => 'East Admin',
        'tenant_admin_email' => 'east.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(7),
    ]));

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.suspend', $university));

    $response->assertRedirect(route('central.universities.index'));
    expect($university->fresh()->status)->toBe('suspended');

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.reactivate', $university));

    $response->assertRedirect(route('central.universities.index'));
    expect($university->fresh()->status)->toBe('active');
});

test('authenticated super admin can extend university subscription', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-extend@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'west-university',
        'name' => 'West University',
        'school_address' => 'West District',
        'tenant_admin_name' => 'West Admin',
        'tenant_admin_email' => 'west.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(5),
    ]));

    $previousExpiry = $university->expires_at;

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.extend', $university), [
        'days' => 20,
    ]);

    $response->assertRedirect(route('central.universities.index'));

    expect($university->fresh()->expires_at->greaterThan($previousExpiry))->toBeTrue();
});

test('authenticated super admin can delete a university', function () {
    Event::fake();

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-delete@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'delete-university',
        'name' => 'Delete University',
        'school_address' => 'Delete District',
        'tenant_admin_name' => 'Delete Admin',
        'tenant_admin_email' => 'delete.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(5),
    ]));

    $response = $this->actingAs($superAdmin, 'super_admin')->delete(route('central.universities.destroy', $university));

    $response->assertRedirect(route('central.universities.index'));
    $this->assertDatabaseMissing('tenants', [
        'id' => 'delete-university',
    ]);
});

test('non super admin cannot access central university management', function () {
    $user = User::factory()->create([
        'role' => 'student_player',
    ]);

    $response = $this->actingAs($user)->get(route('central.universities.index'));

    $response->assertRedirect(route('central.login'));
});
