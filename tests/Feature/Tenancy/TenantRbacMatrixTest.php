<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Game;
use App\Models\GamePlayerAssignment;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TenantRolePermission;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
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

    if (! Schema::hasTable('venues')) {
        Schema::create('venues', function (Blueprint $table): void {
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

    if (! Schema::hasTable('players')) {
        Schema::create('players', function (Blueprint $table): void {
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
        Schema::create('games', function (Blueprint $table): void {
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

    if (! Schema::hasTable('game_player_assignments')) {
        Schema::create('game_player_assignments', function (Blueprint $table): void {
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

    if (! Schema::hasTable('tenant_role_permissions')) {
        Schema::create('tenant_role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role', 50);
            $table->string('permission_key', 120);
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['role', 'permission_key']);
        });
    }

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('tenant admin can open and update rbac matrix', function () {
    $admin = User::factory()->create(['role' => 'university_admin']);

    $this->actingAs($admin)->get(route('tenant.rbac.index'))
        ->assertOk()
        ->assertSeeText('RBAC Matrix (Modular Access Control)');

    $response = $this->actingAs($admin)->put(route('tenant.rbac.update'), [
        'permissions' => [
            'coach.schedules.view' => [
                'team_coach' => false,
                'sports_facilitator' => false,
                'student_player' => false,
            ],
        ],
    ]);

    $response->assertRedirect(route('tenant.rbac.index'));

    $this->assertDatabaseHas('tenant_role_permissions', [
        'role' => 'team_coach',
        'permission_key' => 'coach.schedules.view',
        'is_enabled' => false,
        'updated_by_user_id' => $admin->id,
    ]);
});

test('disabled coach schedules permission blocks coach page', function () {
    User::factory()->create([
        'role' => 'university_admin',
        'id' => 999,
    ]);

    $coach = User::factory()->coach()->create();

    TenantRolePermission::query()->create([
        'role' => 'team_coach',
        'permission_key' => 'coach.schedules.view',
        'is_enabled' => false,
        'updated_by_user_id' => 999,
    ]);

    $this->actingAs($coach)
        ->get(route('tenant.coach.schedules'))
        ->assertForbidden();
});

test('disabled player attendance permission blocks attendance response', function () {
    User::factory()->create([
        'role' => 'university_admin',
        'id' => 998,
    ]);

    $playerUser = User::factory()->player()->create([
        'email' => 'player-rbac@example.test',
    ]);

    $sport = Sport::query()->create(['name' => 'Football', 'code' => 'fball-rbac', 'description' => null, 'is_active' => true]);
    $venue = Venue::query()->create(['name' => 'Field One', 'location' => 'Campus', 'capacity' => 500, 'surface_type' => 'Grass', 'is_active' => true]);
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
        'student_id' => 'RBAC-1001',
        'first_name' => 'Mia',
        'last_name' => 'Cruz',
        'email' => $playerUser->email,
        'position' => 'Forward',
        'is_active' => true,
    ]);

    $assignment = GamePlayerAssignment::query()->create([
        'game_id' => $game->id,
        'team_id' => $teamA->id,
        'player_id' => $playerProfile->id,
        'assigned_by_user_id' => null,
        'is_starter' => false,
        'attendance_status' => 'pending',
        'responded_at' => null,
        'attendance_updated_by_user_id' => null,
    ]);

    TenantRolePermission::query()->create([
        'role' => 'student_player',
        'permission_key' => 'player.attendance.respond',
        'is_enabled' => false,
        'updated_by_user_id' => 998,
    ]);

    $this->actingAs($playerUser)
        ->patch(route('tenant.player.assignments.attendance.update', $assignment), [
            'attendance_status' => 'accepted',
        ])
        ->assertForbidden();
});

test('disabled settings workspace permission blocks settings page', function () {
    User::factory()->create([
        'role' => 'university_admin',
        'id' => 997,
    ]);

    $coach = User::factory()->coach()->create();

    TenantRolePermission::query()->create([
        'role' => 'team_coach',
        'permission_key' => 'common.settings.view',
        'is_enabled' => false,
        'updated_by_user_id' => 997,
    ]);

    $this->actingAs($coach)
        ->get(route('tenant.settings.edit'))
        ->assertForbidden();
});

test('disabled support settings permission blocks support report submission', function () {
    User::factory()->create([
        'role' => 'university_admin',
        'id' => 996,
    ]);

    $coach = User::factory()->coach()->create();

    TenantRolePermission::query()->create([
        'role' => 'team_coach',
        'permission_key' => 'common.settings.support.manage',
        'is_enabled' => false,
        'updated_by_user_id' => 996,
    ]);

    $this->actingAs($coach)
        ->post(route('tenant.settings.support.store'), [
            'category' => 'bug',
            'subject' => 'Blocked by RBAC',
            'message' => 'This request should be blocked by permission middleware.',
        ])
        ->assertForbidden();
});
