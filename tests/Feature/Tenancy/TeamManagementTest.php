<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
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

    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

test('university admin can create and update a team', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $sport = Sport::query()->create([
        'name' => 'Basketball',
        'code' => 'bball',
        'description' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post(route('tenant.teams.store'), [
        'sport_id' => $sport->id,
        'name' => 'Falcons',
        'coach_name' => 'John Doe',
        'coach_email' => 'john@example.com',
        'division' => 'A',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.teams.index'));
    $this->assertDatabaseHas('teams', ['name' => 'Falcons', 'sport_id' => $sport->id]);

    $teamId = Team::query()->where('name', 'Falcons')->firstOrFail()->id;

    $response = $this->actingAs($user)->put(route('tenant.teams.update', $teamId), [
        'sport_id' => $sport->id,
        'name' => 'Falcons Elite',
        'coach_name' => 'John Doe',
        'coach_email' => 'john@example.com',
        'division' => 'A',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('tenant.teams.index'));
    $this->assertDatabaseHas('teams', ['id' => $teamId, 'name' => 'Falcons Elite']);
});

test('student player is forbidden from team management routes', function () {
    $user = User::factory()->create(['role' => 'student_player']);

    $response = $this->actingAs($user)->get(route('tenant.teams.index'));

    $response->assertForbidden();
});

test('university admin can delete team', function () {
    $user = User::factory()->create(['role' => 'university_admin']);

    $sport = Sport::query()->create([
        'name' => 'Volleyball',
        'code' => 'vball',
        'description' => null,
        'is_active' => true,
    ]);

    $team = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Spikers',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->delete(route('tenant.teams.destroy', $team));

    $response->assertRedirect(route('tenant.teams.index'));
    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});
