<?php

use App\Models\Plan;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ]);

    Plan::query()->updateOrCreate(['code' => 'pro'], [
        'name' => 'Pro',
        'feature_flags' => ['bracket' => true, 'analytics' => true, 'exports' => true],
        'is_active' => true,
    ]);
});

afterEach(function () {
    $tenantId = tenant()?->id;
    
    if (tenant() !== null) {
        tenancy()->end();
    }

    if ($tenantId) {
        $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenantId.(string) config('tenancy.database.suffix', ''));

        if (is_file($databasePath)) {
            @unlink($databasePath);
        }
    }
});

function initializeTenantForModules(University $tenant): void
{
    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenant->id.(string) config('tenancy.database.suffix', ''));

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    tenancy()->initialize($tenant);

    // Schema is handled by migrations in real app, but for unit test we can use minimal
    if (! Schema::hasTable('sports')) {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('teams')) {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id');
            $table->string('name');
            $table->string('coach_email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('games')) {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id');
            $table->foreignId('home_team_id');
            $table->foreignId('away_team_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_score')->nullable();
            $table->timestamps();
        });
    }
}

test('coach can access schedules page', function () {
    $user = User::factory()->create(['role' => 'team_coach']);
    $tenantId = 'mod-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Module Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForModules($tenant);

    $response = $this->actingAs($user)->get(route('tenant.coach.schedules'));

    $response->assertStatus(200);
});

test('coach can access my team page', function () {
    $user = User::factory()->create(['role' => 'team_coach']);
    $tenantId = 'mod-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Module Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForModules($tenant);

    $response = $this->actingAs($user)->get(route('tenant.coach.my-team'));

    $response->assertStatus(200);
});

test('player can access my schedule page', function () {
    $user = User::factory()->create(['role' => 'student_player']);
    $tenantId = 'mod-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Module Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForModules($tenant);

    // Standings calculation needs at least sports table
    if (! Schema::hasTable('players')) {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable();
            $table->string('email')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });
    }

    $response = $this->actingAs($user)->get(route('tenant.player.my-schedule'));

    $response->assertStatus(200);
});
