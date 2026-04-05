<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Game;
use App\Models\GamePlayerAssignment;
use App\Models\Player;
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

    if (! Schema::hasTable('players')) {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('position')->nullable();
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

    if (! Schema::hasTable('game_team_participations')) {
        Schema::create('game_team_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamp('coach_confirmed_at')->nullable();
            $table->foreignId('coach_confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('coach_note')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'team_id']);
        });
    }

    if (! Schema::hasTable('game_player_assignments')) {
        Schema::create('game_player_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_starter')->default(false);
            $table->string('attendance_status')->default('pending');
            $table->dateTime('responded_at')->nullable();
            $table->foreignId('attendance_updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['game_id', 'player_id']);
        });
    }

    if (! Schema::hasTable('team_announcements')) {
        Schema::create('team_announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('coach can manage lineup and confirm team participation', function () {
    $coach = User::factory()->coach()->create([
        'email' => 'coach1@example.test',
    ]);

    $sport = Sport::query()->create(['name' => 'Basketball', 'code' => 'bball-cpe', 'description' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Main Gym', 'location' => 'Campus', 'capacity' => 500, 'surface_type' => 'Wood', 'is_active' => true]);

    $myTeam = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Falcons',
        'coach_name' => 'Coach One',
        'coach_email' => $coach->email,
        'division' => null,
        'is_active' => true,
    ]);

    $opponent = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Wolves',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $myTeam->id,
        'away_team_id' => $opponent->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $playerA = Player::query()->create(['team_id' => $myTeam->id, 'student_id' => 'P-1001', 'first_name' => 'Ana', 'last_name' => 'Lopez', 'email' => 'ana@example.test', 'position' => 'Guard', 'is_active' => true]);
    $playerB = Player::query()->create(['team_id' => $myTeam->id, 'student_id' => 'P-1002', 'first_name' => 'Ben', 'last_name' => 'Yao', 'email' => 'ben@example.test', 'position' => 'Forward', 'is_active' => true]);

    $response = $this->actingAs($coach)->post(route('tenant.coach.games.lineup.update', $game), [
        'player_ids' => [$playerA->id, $playerB->id],
        'starter_player_ids' => [$playerA->id],
        'coach_note' => 'Ready for game day',
        'confirm_team' => 1,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('game_team_participations', [
        'game_id' => $game->id,
        'team_id' => $myTeam->id,
        'coach_confirmed_by_user_id' => $coach->id,
        'coach_note' => 'Ready for game day',
    ]);

    $this->assertDatabaseHas('game_player_assignments', [
        'game_id' => $game->id,
        'team_id' => $myTeam->id,
        'player_id' => $playerA->id,
        'assigned_by_user_id' => $coach->id,
        'is_starter' => true,
        'attendance_status' => 'pending',
    ]);

    $this->assertDatabaseHas('game_player_assignments', [
        'game_id' => $game->id,
        'team_id' => $myTeam->id,
        'player_id' => $playerB->id,
        'assigned_by_user_id' => $coach->id,
        'is_starter' => false,
        'attendance_status' => 'pending',
    ]);
});

test('coach cannot manage lineup for a game outside their team', function () {
    $coach = User::factory()->coach()->create([
        'email' => 'coach2@example.test',
    ]);

    $sport = Sport::query()->create(['name' => 'Volleyball', 'code' => 'vball-cpe', 'description' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Secondary Gym', 'location' => 'Campus', 'capacity' => 300, 'surface_type' => 'Wood', 'is_active' => true]);

    Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Assigned Team',
        'coach_name' => 'Coach Two',
        'coach_email' => $coach->email,
        'division' => null,
        'is_active' => true,
    ]);

    $coachTeam = Team::query()->where('coach_email', $coach->email)->firstOrFail();
    $coachPlayer = Player::query()->create([
        'team_id' => $coachTeam->id,
        'student_id' => 'P-1999',
        'first_name' => 'Coach',
        'last_name' => 'Member',
        'email' => 'coach-member@example.test',
        'position' => 'Guard',
        'is_active' => true,
    ]);

    $teamA = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Spikers A', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $teamB = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Spikers B', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $teamA->id,
        'away_team_id' => $teamB->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDays(2),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $response = $this->actingAs($coach)->post(route('tenant.coach.games.lineup.update', $game), [
        'player_ids' => [$coachPlayer->id],
    ]);

    $response->assertForbidden();
});

test('player can confirm attendance for own assignment', function () {
    $playerUser = User::factory()->player()->create([
        'email' => 'player1@example.test',
    ]);

    $sport = Sport::query()->create(['name' => 'Football', 'code' => 'fball-cpe', 'description' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Field One', 'location' => 'Campus', 'capacity' => 1200, 'surface_type' => 'Grass', 'is_active' => true]);

    $teamA = Team::query()->create(['sport_id' => $sport->id, 'name' => 'United', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $teamB = Team::query()->create(['sport_id' => $sport->id, 'name' => 'City', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $teamA->id,
        'away_team_id' => $teamB->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    $playerProfile = Player::query()->create([
        'team_id' => $teamA->id,
        'student_id' => 'P-2001',
        'first_name' => 'Mia',
        'last_name' => 'Chen',
        'email' => $playerUser->email,
        'position' => 'Midfielder',
        'is_active' => true,
    ]);

    $assignment = GamePlayerAssignment::query()->create([
        'game_id' => $game->id,
        'team_id' => $teamA->id,
        'player_id' => $playerProfile->id,
        'assigned_by_user_id' => null,
        'is_starter' => true,
        'attendance_status' => 'pending',
        'responded_at' => null,
        'attendance_updated_by_user_id' => null,
    ]);

    $response = $this->actingAs($playerUser)->patch(route('tenant.player.assignments.attendance.update', $assignment), [
        'attendance_status' => 'accepted',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('game_player_assignments', [
        'id' => $assignment->id,
        'attendance_status' => 'accepted',
        'attendance_updated_by_user_id' => $playerUser->id,
    ]);
});

test('player cannot update another players attendance assignment', function () {
    $playerUser = User::factory()->player()->create([
        'email' => 'player2@example.test',
    ]);

    $sport = Sport::query()->create(['name' => 'Table Tennis', 'code' => 'tt-cpe', 'description' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Hall A', 'location' => 'Campus', 'capacity' => 100, 'surface_type' => 'Hard', 'is_active' => true]);
    $teamA = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Smashers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);
    $teamB = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Servers', 'coach_name' => null, 'coach_email' => null, 'division' => null, 'is_active' => true]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $teamA->id,
        'away_team_id' => $teamB->id,
        'venue_id' => $venue->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);

    Player::query()->create([
        'team_id' => $teamA->id,
        'student_id' => 'P-3001',
        'first_name' => 'Your',
        'last_name' => 'Player',
        'email' => $playerUser->email,
        'position' => 'Starter',
        'is_active' => true,
    ]);

    $otherPlayer = Player::query()->create([
        'team_id' => $teamA->id,
        'student_id' => 'P-3002',
        'first_name' => 'Other',
        'last_name' => 'Player',
        'email' => 'other@example.test',
        'position' => 'Bench',
        'is_active' => true,
    ]);

    $assignment = GamePlayerAssignment::query()->create([
        'game_id' => $game->id,
        'team_id' => $teamA->id,
        'player_id' => $otherPlayer->id,
        'assigned_by_user_id' => null,
        'is_starter' => false,
        'attendance_status' => 'pending',
        'responded_at' => null,
        'attendance_updated_by_user_id' => null,
    ]);

    $response = $this->actingAs($playerUser)->patch(route('tenant.player.assignments.attendance.update', $assignment), [
        'attendance_status' => 'declined',
    ]);

    $response->assertForbidden();
});
