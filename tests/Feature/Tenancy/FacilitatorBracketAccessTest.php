<?php

use App\Models\BracketMatch;
use App\Models\Plan;
use App\Models\Sport;
use App\Models\Team;
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

function initializeTenantForFacilitator(University $tenant): void
{
    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenant->id.(string) config('tenancy.database.suffix', ''));

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    tenancy()->initialize($tenant);

    // Minimal schema for test
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
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('bracket_matches')) {
        Schema::create('bracket_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id');
            $table->unsignedInteger('round_number');
            $table->unsignedInteger('match_number');
            $table->foreignId('home_team_id')->nullable();
            $table->foreignId('away_team_id')->nullable();
            $table->string('home_slot_label')->nullable();
            $table->string('away_slot_label')->nullable();
            $table->foreignId('winner_team_id')->nullable();
            $table->dateTime('played_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('bracket_match_audits')) {
        Schema::create('bracket_match_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bracket_match_id');
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->foreignId('previous_winner_team_id')->nullable();
            $table->foreignId('new_winner_team_id')->nullable();
            $table->timestamps();
        });
    }
}

test('sports facilitator can access bracket page', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);
    $tenantId = 'fac-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Facilitator Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForFacilitator($tenant);

    $response = $this->actingAs($user)->get(route('tenant.pro.bracket'));

    $response->assertStatus(200);
});

test('sports facilitator can access analytics page', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);
    $tenantId = 'fac-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Facilitator Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForFacilitator($tenant);

    $response = $this->actingAs($user)->get(route('tenant.pro.analytics'));

    $response->assertStatus(200);
});

test('sports facilitator can record bracket result', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);
    $tenantId = 'fac-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Facilitator Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenantForFacilitator($tenant);

    $sport = Sport::query()->create(['code' => 'box', 'name' => 'Boxing']);
    $team1 = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Team 1']);
    $team2 = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Team 2']);

    $match = BracketMatch::query()->create([
        'sport_id' => $sport->id,
        'round_number' => 1,
        'match_number' => 1,
        'home_team_id' => $team1->id,
        'away_team_id' => $team2->id,
    ]);

    $response = $this->actingAs($user)->patch(route('tenant.pro.bracket.matches.winner', $match), [
        'winner_team_id' => $team1->id,
    ]);

    $response->assertRedirect();
    expect($match->fresh()->winner_team_id)->toBe($team1->id);
});
