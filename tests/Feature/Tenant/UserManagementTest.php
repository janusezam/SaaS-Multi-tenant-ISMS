<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('university admin can create tenant users', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $response = $this->actingAs($admin)->post(route('tenant.users.store'), [
        'name' => 'Coach User',
        'email' => 'coach@example.com',
        'role' => 'team_coach',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('tenant.users.index'));
    $response->assertSessionHas('status', 'Tenant user added successfully.');

    $created = User::query()->where('email', 'coach@example.com')->first();

    expect($created)->not()->toBeNull();
    expect($created?->role)->toBe('team_coach');
    expect(Hash::check('password123', (string) $created?->password))->toBeTrue();
});

test('university admin can view tenant users index', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $response = $this->actingAs($admin)->get(route('tenant.users.index'));

    $response->assertOk();
    $response->assertSee('Pending Account Requests');
    $response->assertSee('No pending account requests.');
});

test('non admin user is forbidden from tenant user management', function () {
    $facilitator = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $response = $this->actingAs($facilitator)->get(route('tenant.users.index'));

    $response->assertForbidden();
});

test('university admin can update tenant user without changing password', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $player = User::factory()->create([
        'role' => 'student_player',
        'password' => 'initial-password',
    ]);

    $currentHash = (string) $player->password;

    $response = $this->actingAs($admin)->put(route('tenant.users.update', $player), [
        'name' => 'Updated Player',
        'email' => $player->email,
        'role' => 'student_player',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect(route('tenant.users.index'));

    $player->refresh();

    expect($player->name)->toBe('Updated Player');
    expect((string) $player->password)->toBe($currentHash);
});

test('university admin can delete non admin tenant user', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $coach = User::factory()->create([
        'role' => 'team_coach',
    ]);

    $response = $this->actingAs($admin)->delete(route('tenant.users.destroy', $coach));

    $response->assertRedirect(route('tenant.users.index'));
    $response->assertSessionHas('status', 'Tenant user removed successfully.');
    $this->assertDatabaseMissing('users', [
        'id' => $coach->id,
    ]);
});

test('university admin cannot delete their own account', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $response = $this->actingAs($admin)->delete(route('tenant.users.destroy', $admin));

    $response->assertRedirect(route('tenant.users.index'));
    $response->assertSessionHas('status', 'You cannot delete your own account.');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('university admin cannot delete another university admin account', function () {
    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $otherAdmin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $response = $this->actingAs($admin)->delete(route('tenant.users.destroy', $otherAdmin));

    $response->assertRedirect(route('tenant.users.index'));
    $response->assertSessionHas('status', 'University admin accounts cannot be deleted here.');
    $this->assertDatabaseHas('users', [
        'id' => $otherAdmin->id,
    ]);
});
