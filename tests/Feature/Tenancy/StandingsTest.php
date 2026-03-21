<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use App\Support\StandingsCalculator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    if (! Schema::hasTable('sports')) {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('venues')) {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->integer('capacity')->default(0);
            $table->string('surface_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('teams')) {
        Schema::create('teams', function (Blueprint $table) {
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

    if (! Schema::hasTable('games')) {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('status')->default('scheduled');
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_score')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('game_result_audits')) {
        Schema::create('game_result_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->string('previous_status');
            $table->string('new_status');
            $table->unsignedInteger('previous_home_score')->nullable();
            $table->unsignedInteger('new_home_score')->nullable();
            $table->unsignedInteger('previous_away_score')->nullable();
            $table->unsignedInteger('new_away_score')->nullable();
            $table->timestamps();
        });
    }

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('facilitator can submit game result', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);
    $sport = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Falcons', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Wolves', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Main Court', 'location' => 'North', 'capacity' => 1000, 'surface_type' => 'Hardwood', 'is_active' => true]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $response = $this->actingAs($user)->patch(route('tenant.games.result', $game), [
        'status' => 'completed',
        'home_score' => 82,
        'away_score' => 79,
    ]);

    $response->assertRedirect(route('tenant.games.index'));
    $this->assertDatabaseHas('games', ['id' => $game->id, 'status' => 'completed', 'home_score' => 82, 'away_score' => 79]);
    $this->assertDatabaseHas('game_result_audits', [
        'game_id' => $game->id,
        'changed_by_user_id' => $user->id,
        'previous_status' => 'scheduled',
        'new_status' => 'completed',
        'previous_home_score' => null,
        'new_home_score' => 82,
        'previous_away_score' => null,
        'new_away_score' => 79,
    ]);
});

test('student player cannot submit game result', function () {
    $user = User::factory()->create(['role' => 'student_player']);
    $sport = Sport::query()->create(['name' => 'Volleyball', 'code' => 'vball', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'A', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'B', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Gym', 'location' => 'Main', 'capacity' => 400, 'surface_type' => 'Wood', 'is_active' => true]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $response = $this->actingAs($user)->patch(route('tenant.games.result', $game), [
        'status' => 'completed',
        'home_score' => 1,
        'away_score' => 0,
    ]);

    $response->assertForbidden();
});

test('standings calculator ranks teams by points then goal difference', function () {
    $sport = Sport::query()->create(['name' => 'Football', 'code' => 'fball', 'description' => null, 'is_active' => true]);
    $teamA = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Alpha', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $teamB = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Bravo', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $teamC = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Charlie', 'coach_name' => null, 'coach_email' => null, 'division' => 'A', 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Field', 'location' => 'West', 'capacity' => 700, 'surface_type' => 'Grass', 'is_active' => true]);

    Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $teamA->id,
        'away_team_id' => $teamB->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->subDays(2),
        'status' => 'completed',
        'home_score' => 3,
        'away_score' => 1,
    ]);

    Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $teamC->id,
        'away_team_id' => $teamA->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->subDay(),
        'status' => 'completed',
        'home_score' => 0,
        'away_score' => 2,
    ]);

    $standings = app(StandingsCalculator::class)->calculate(Game::query()->with(['homeTeam', 'awayTeam'])->get());

    expect($standings[0]['team'])->toBe('Alpha');
    expect($standings[0]['points'])->toBe(6);
});

test('authenticated user can view standings page', function () {
    $user = User::factory()->create(['role' => 'student_player']);

    $response = $this->actingAs($user)->get(route('tenant.standings.index'));

    $response->assertOk();
    $response->assertSee('Live Standings');
});
