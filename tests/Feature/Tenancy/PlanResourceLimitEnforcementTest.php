<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Plan;
use App\Models\Sport;
use App\Models\Team;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

function limitTenantDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'plan-limit-tenant'.(string) config('tenancy.database.suffix');

    return database_path($databaseName);
}

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    $databasePath = limitTenantDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializeLimitTenantWithSchema(): University
{
    $databasePath = limitTenantDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'plan-limit-tenant',
        'name' => 'Plan Limit University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]));

    tenancy()->initialize($tenant);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('student_player');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('sports')) {
        Schema::create('sports', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('teams')) {
        Schema::create('teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->string('name');
            $table->string('coach_name')->nullable();
            $table->string('coach_email')->nullable();
            $table->string('division')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    return $tenant;
}

test('tenant user creation is blocked when plan user limit is reached', function () {
    $basicPlan = Plan::query()->where('code', 'basic')->firstOrFail();
    $basicPlan->update([
        'max_users' => 1,
    ]);

    initializeLimitTenantWithSchema();

    $admin = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-admin@example.test',
        'role' => 'university_admin',
        'password' => 'password',
    ]);

    $response = $this->actingAs($admin)->post(route('tenant.users.store'), [
        'name' => 'Coach User',
        'email' => 'coach-limit@example.test',
        'role' => 'team_coach',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('tenant.users.index'));
    $response->assertSessionHas('status');

    $this->assertDatabaseMissing('users', [
        'email' => 'coach-limit@example.test',
    ]);
});

test('team creation is blocked when plan team limit is reached', function () {
    $basicPlan = Plan::query()->where('code', 'basic')->firstOrFail();
    $basicPlan->update([
        'max_teams' => 1,
    ]);

    initializeLimitTenantWithSchema();

    $admin = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-team-admin@example.test',
        'role' => 'university_admin',
        'password' => 'password',
    ]);

    $sportId = Sport::query()->create([
        'name' => 'Basketball',
        'code' => 'limit-bball',
        'description' => null,
        'is_active' => true,
    ])->id;

    Team::query()->create([
        'sport_id' => $sportId,
        'name' => 'Existing Team',
        'coach_name' => null,
        'coach_email' => null,
        'division' => 'A',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('tenant.teams.store'), [
        'sport_id' => $sportId,
        'name' => 'Blocked Team',
        'coach_name' => 'Coach',
        'coach_email' => 'coach@example.test',
        'division' => 'A',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.teams.index'));
    $response->assertSessionHas('status');

    $this->assertDatabaseMissing('teams', [
        'name' => 'Blocked Team',
    ]);
});

test('sport creation is blocked when plan sport limit is reached', function () {
    $basicPlan = Plan::query()->where('code', 'basic')->firstOrFail();
    $basicPlan->update([
        'max_sports' => 1,
    ]);

    initializeLimitTenantWithSchema();

    $admin = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-sport-admin@example.test',
        'role' => 'university_admin',
        'password' => 'password',
    ]);

    Sport::query()->create([
        'name' => 'Volleyball',
        'code' => 'limit-vball',
        'description' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('tenant.sports.store'), [
        'name' => 'Blocked Sport',
        'code' => 'blocked-sport',
        'description' => 'Should be blocked',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.sports.index'));
    $response->assertSessionHas('status');

    $this->assertDatabaseMissing('sports', [
        'code' => 'blocked-sport',
    ]);
});
