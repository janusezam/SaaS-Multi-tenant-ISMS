<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Game;
use App\Models\GameResultAudit;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
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

test('sports facilitator can schedule and update a game', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $sport = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Falcons', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Wolves', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Main Court', 'location' => 'North Campus', 'capacity' => 1000, 'surface_type' => 'Hardwood', 'is_active' => true]);

    $response = $this->actingAs($user)->post(route('tenant.games.store'), [
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $response->assertRedirect(route('tenant.games.index'));
    $this->assertDatabaseHas('games', ['sport_id' => $sport->id, 'home_team_id' => $homeTeam->id, 'away_team_id' => $awayTeam->id]);

    $gameId = Game::query()->firstOrFail()->id;

    $response = $this->actingAs($user)->put(route('tenant.games.update', $gameId), [
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'status' => 'completed',
        'home_score' => 75,
        'away_score' => 70,
    ]);

    $response->assertRedirect(route('tenant.games.index'));
    $this->assertDatabaseHas('games', ['id' => $gameId, 'status' => 'completed', 'home_score' => 75, 'away_score' => 70]);
});

test('games sport filter tabs are dynamic per tenant sports', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    Sport::query()->create([
        'name' => 'Futsal',
        'code' => 'futsal-gm',
        'description' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('tenant.games.index'));

    $response->assertOk();
    $response->assertSee('All Sports');
    $response->assertSee('Futsal');
    $response->assertDontSee('Basketball');
    $response->assertDontSee('Volleyball');
    $response->assertDontSee('Football');
    $response->assertDontSee('Badminton');
});

test('validation prevents same team as home and away', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $sport = Sport::query()->create(['name' => 'Volleyball', 'code' => 'vball', 'description' => null, 'is_active' => true]);
    $team = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Spikers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Gym A', 'location' => 'Main', 'capacity' => 600, 'surface_type' => 'Wood', 'is_active' => true]);

    $response = $this->actingAs($user)->from(route('tenant.games.create'))->post(route('tenant.games.store'), [
        'sport_id' => $sport->id,
        'home_team_id' => $team->id,
        'away_team_id' => $team->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
    ]);

    $response->assertSessionHasErrors(['home_team_id', 'away_team_id']);
});

test('validation prevents selecting teams from different sport', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $basketball = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball-mix', 'description' => null, 'is_active' => true]);
    $volleyball = Sport::query()->create(['name' => 'Volleyball', 'code' => 'vball-mix', 'description' => null, 'is_active' => true]);

    $homeTeam = Team::query()->create(['sport_id' => $basketball->id, 'name' => 'Mix Falcons', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $awayTeamWrongSport = Team::query()->create(['sport_id' => $volleyball->id, 'name' => 'Mix Blockers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Mix Court', 'location' => 'Main', 'capacity' => 700, 'surface_type' => 'Wood', 'is_active' => true]);

    $response = $this->actingAs($user)->from(route('tenant.games.create'))->post(route('tenant.games.store'), [
        'sport_id' => $basketball->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeamWrongSport->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
    ]);

    $response->assertSessionHasErrors(['away_team_id']);
});

test('student player is forbidden from game management routes', function () {
    $user = User::factory()->create(['role' => 'student_player']);

    $response = $this->actingAs($user)->get(route('tenant.games.index'));

    $response->assertForbidden();
});

test('university admin can delete game', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $sport = Sport::query()->create(['name' => 'Football', 'code' => 'fball', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'United', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'City', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Field One', 'location' => 'West', 'capacity' => 1500, 'surface_type' => 'Grass', 'is_active' => true]);

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

    $response = $this->actingAs($user)->delete(route('tenant.games.destroy', $game));

    $response->assertRedirect(route('tenant.games.index'));
    $this->assertDatabaseMissing('games', ['id' => $game->id]);
});

test('sports facilitator can view game audit trail page', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $sport = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball2', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Falcons B', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Wolves B', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Aux Court', 'location' => 'South Campus', 'capacity' => 800, 'surface_type' => 'Hardwood', 'is_active' => true]);

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

    GameResultAudit::query()->create([
        'game_id' => $game->id,
        'changed_by_user_id' => $user->id,
        'previous_status' => 'scheduled',
        'new_status' => 'completed',
        'previous_home_score' => null,
        'new_home_score' => 70,
        'previous_away_score' => null,
        'new_away_score' => 68,
    ]);

    $response = $this->actingAs($user)->get(route('tenant.games.audits', $game));

    $response->assertOk();
    $response->assertSee('Game Audit Trail');
    $response->assertSee('SCHEDULED');
    $response->assertSee('COMPLETED');
});

test('completed games do not show submit result action but keep edit action', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $sport = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball4', 'description' => null, 'is_active' => true]);
    $homeTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Falcons C', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $awayTeam = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Wolves C', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Main Court C', 'location' => 'North Campus', 'capacity' => 1000, 'surface_type' => 'Hardwood', 'is_active' => true]);

    Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->subDay(),
        'status' => 'completed',
        'home_score' => 78,
        'away_score' => 70,
    ]);

    $response = $this->actingAs($user)->get(route('tenant.games.index'));

    $response->assertOk();
    $response->assertDontSee('Submit Result');
    $response->assertSee('Edit');
    $response->assertSee('Result already submitted. You can still use game actions below.');
});

test('sports facilitator can view result audits index and filter by sport', function () {
    $user = User::factory()->create(['role' => 'sports_facilitator']);

    $basketball = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball3', 'description' => null, 'is_active' => true]);
    $volleyball = Sport::query()->create(['name' => 'Volleyball', 'code' => 'vball2', 'description' => null, 'is_active' => true]);

    $venue = Venue::query()->create(['name' => 'Main Dome', 'location' => 'Campus', 'capacity' => 1000, 'surface_type' => 'Hardwood', 'is_active' => true]);

    $basketballHome = Team::query()->create(['sport_id' => $basketball->id, 'name' => 'B Hawks', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $basketballAway = Team::query()->create(['sport_id' => $basketball->id, 'name' => 'B Foxes', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $volleyballHome = Team::query()->create(['sport_id' => $volleyball->id, 'name' => 'V Spikers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $volleyballAway = Team::query()->create(['sport_id' => $volleyball->id, 'name' => 'V Blockers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);

    $basketballGame = Game::query()->create([
        'sport_id' => $basketball->id,
        'home_team_id' => $basketballHome->id,
        'away_team_id' => $basketballAway->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $volleyballGame = Game::query()->create([
        'sport_id' => $volleyball->id,
        'home_team_id' => $volleyballHome->id,
        'away_team_id' => $volleyballAway->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDays(2),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    GameResultAudit::query()->create([
        'game_id' => $basketballGame->id,
        'changed_by_user_id' => $user->id,
        'previous_status' => 'scheduled',
        'new_status' => 'completed',
        'previous_home_score' => null,
        'new_home_score' => 80,
        'previous_away_score' => null,
        'new_away_score' => 72,
    ]);

    GameResultAudit::query()->create([
        'game_id' => $volleyballGame->id,
        'changed_by_user_id' => $user->id,
        'previous_status' => 'scheduled',
        'new_status' => 'completed',
        'previous_home_score' => null,
        'new_home_score' => 3,
        'previous_away_score' => null,
        'new_away_score' => 1,
    ]);

    $response = $this->actingAs($user)->get(route('tenant.audits.game-results.index'));
    $response->assertOk();
    $response->assertSee('Result Audit History');
    $response->assertSee('B Hawks');
    $response->assertSee('V Spikers');

    $filtered = $this->actingAs($user)->get(route('tenant.audits.game-results.index', [
        'sport_id' => $basketball->id,
    ]));

    $filtered->assertOk();
    $filtered->assertSee('B Hawks');
    $filtered->assertDontSee('V Spikers');
});

test('student player is forbidden from result audits index', function () {
    $user = User::factory()->create(['role' => 'student_player']);

    $response = $this->actingAs($user)->get(route('tenant.audits.game-results.index'));

    $response->assertForbidden();
});
