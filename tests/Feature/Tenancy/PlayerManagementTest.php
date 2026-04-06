<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
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

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('university admin can create and update a player', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $sport = Sport::query()->create([
        'name' => 'Football',
        'code' => 'fball',
        'description' => null,
        'is_active' => true,
    ]);

    $team = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'United',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('tenant.players.store'), [
        'team_id' => $team->id,
        'student_id' => 'STU-0001',
        'first_name' => 'Alex',
        'last_name' => 'Reyes',
        'email' => 'alex@example.com',
        'position' => 'Forward',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.players.index'));
    $this->assertDatabaseHas('players', ['student_id' => 'STU-0001', 'team_id' => $team->id]);

    $playerId = Player::query()->where('student_id', 'STU-0001')->firstOrFail()->id;

    $response = $this->actingAs($user)->put(route('tenant.players.update', $playerId), [
        'team_id' => $team->id,
        'student_id' => 'STU-0001',
        'first_name' => 'Alex',
        'last_name' => 'Rivera',
        'email' => 'alex@example.com',
        'position' => 'Midfielder',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.players.index'));
    $this->assertDatabaseHas('players', ['id' => $playerId, 'last_name' => 'Rivera', 'position' => 'Midfielder']);
});

test('student player is forbidden from player management routes', function () {
    $user = User::factory()->create(['role' => 'student_player']);

    $response = $this->actingAs($user)->get(route('tenant.players.index'));

    $response->assertForbidden();
});

test('university admin can delete player', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $sport = Sport::query()->create([
        'name' => 'Tennis',
        'code' => 'tennis',
        'description' => null,
        'is_active' => true,
    ]);

    $team = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Aces',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $player = Player::query()->create([
        'team_id' => $team->id,
        'student_id' => 'STU-0099',
        'first_name' => 'Mia',
        'last_name' => 'Cruz',
        'email' => null,
        'position' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->delete(route('tenant.players.destroy', $player));

    $response->assertRedirect(route('tenant.players.index'));
    $this->assertDatabaseMissing('players', ['id' => $player->id]);
});

test('university admin can assign player from existing player user dropdown', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $playerUser = User::factory()->create([
        'name' => 'Jordan Delta',
        'email' => 'jordan-delta@example.test',
        'role' => 'student_player',
    ]);

    $sport = Sport::query()->create([
        'name' => 'Handball',
        'code' => 'hball',
        'description' => null,
        'is_active' => true,
    ]);

    $team = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Handball United',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('tenant.players.store'), [
        'team_id' => $team->id,
        'player_user_id' => $playerUser->id,
        'student_id' => 'STU-7001',
        'position' => 'Defender',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.players.index'));

    $this->assertDatabaseHas('players', [
        'team_id' => $team->id,
        'student_id' => 'STU-7001',
        'first_name' => 'Jordan',
        'last_name' => 'Delta',
        'email' => 'jordan-delta@example.test',
    ]);
});
