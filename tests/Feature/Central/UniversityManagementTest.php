<?php

use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\University;
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
    $response->assertSee('School Management');
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

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => 'nsu',
        'plan' => 'pro',
        'status' => 'active',
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

    Subscription::query()->create([
        'tenant_id' => $university->id,
        'plan' => 'basic',
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.suspend', $university));

    $response->assertRedirect(route('central.universities.index'));
    expect($university->fresh()->status)->toBe('suspended');
    expect($university->fresh()->subscription?->status)->toBe('expired');

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.reactivate', $university));

    $response->assertRedirect(route('central.universities.index'));
    expect($university->fresh()->status)->toBe('active');
    expect($university->fresh()->subscription?->status)->toBe('active');
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

    Subscription::query()->create([
        'tenant_id' => $university->id,
        'plan' => 'basic',
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    $previousExpiry = $university->expires_at;

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.extend', $university), [
        'days' => 20,
    ]);

    $response->assertRedirect(route('central.universities.index'));

    expect($university->fresh()->expires_at->greaterThan($previousExpiry))->toBeTrue();
    expect($university->fresh()->subscription?->due_date?->isFuture())->toBeTrue();
});

test('super admin can approve pending university and activate subscription', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-approve@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'approve-university',
        'name' => 'Approve University',
        'school_address' => 'Approve District',
        'tenant_admin_name' => 'Approve Admin',
        'tenant_admin_email' => 'approve.admin@example.test',
        'plan' => 'basic',
        'status' => 'pending',
        'subscription_starts_at' => null,
        'expires_at' => null,
    ]));

    $university->domains()->create([
        'domain' => 'approve.isms.test',
    ]);

    Subscription::query()->create([
        'tenant_id' => $university->id,
        'plan' => 'basic',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->patch(route('central.universities.approve', $university));

    $response->assertRedirect(route('central.universities.index'));

    expect($university->fresh()->status)->toBe('active');
    expect($university->fresh()->subscription?->status)->toBe('active');
});

test('approved university cannot be set back to pending from edit update', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-status-guard@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'status-guard-university',
        'name' => 'Status Guard University',
        'school_address' => 'Status District',
        'tenant_admin_name' => 'Status Admin',
        'tenant_admin_email' => 'status.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(30),
    ]));

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->from(route('central.universities.edit', $university))
        ->put(route('central.universities.update', $university), [
            'name' => 'Status Guard University',
            'school_address' => 'Status District',
            'tenant_admin_name' => 'Status Admin',
            'tenant_admin_email' => 'status.admin@example.test',
            'plan' => 'basic',
            'status' => 'pending',
            'subscription_starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(30)->toDateString(),
        ]);

    $response->assertRedirect(route('central.universities.edit', $university));
    $response->assertSessionHasErrors('status');
    expect($university->fresh()->status)->toBe('active');
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

test('creating a university does not require manual tenant admin password entry', function () {
    Event::fake();

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'central-admin-password-validation@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->from(route('central.universities.create'))
        ->post(route('central.universities.store'), [
            'name' => 'Validation University',
            'school_address' => 'Validation District',
            'tenant_admin_name' => 'Validation Admin',
            'tenant_admin_email' => 'validation.admin@example.test',
            'subdomain' => 'validation',
            'plan' => 'basic',
        ]);

    $response->assertRedirect(route('central.universities.index'));
    $response->assertSessionHasNoErrors();
});
