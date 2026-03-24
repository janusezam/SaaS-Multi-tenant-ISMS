<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\BracketMatch;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use App\Models\University;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

function lifecycleTenantDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'lifecycle-tenant'.(string) config('tenancy.database.suffix');

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

    $databasePath = lifecycleTenantDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializeLifecycleTenant(): void
{
    $databasePath = lifecycleTenantDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'lifecycle-tenant',
        'name' => 'Lifecycle University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]));

    tenancy()->initialize($tenant);

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

    if (! Schema::hasTable('game_result_audits')) {
        Schema::create('game_result_audits', function (Blueprint $table): void {
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

    if (! Schema::hasTable('bracket_matches')) {
        Schema::create('bracket_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->unsignedInteger('match_number');
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('home_slot_label')->nullable();
            $table->string('away_slot_label')->nullable();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->dateTime('played_at')->nullable();
            $table->timestamps();

            $table->unique(['sport_id', 'round_number', 'match_number']);
        });
    }

    if (! Schema::hasTable('bracket_match_audits')) {
        Schema::create('bracket_match_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bracket_match_id')->constrained('bracket_matches')->cascadeOnDelete();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->foreignId('previous_winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('new_winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
        });
    }
}

test('tenant lifecycle flow works from setup to pro bracket and exports', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    initializeLifecycleTenant();

    $sportResponse = $this->actingAs($user)->post(route('tenant.sports.store'), [
        'name' => 'Basketball',
        'code' => 'life-bball',
        'description' => 'Season play',
        'is_active' => true,
    ]);
    $sportResponse->assertRedirect(route('tenant.sports.index'));

    $sport = Sport::query()->firstOrFail();

    $venueResponse = $this->actingAs($user)->post(route('tenant.venues.store'), [
        'name' => 'Main Arena',
        'location' => 'North Campus',
        'capacity' => 1200,
        'surface_type' => 'Hardwood',
        'is_active' => true,
    ]);
    $venueResponse->assertRedirect(route('tenant.venues.index'));

    $teamAResponse = $this->actingAs($user)->post(route('tenant.teams.store'), [
        'sport_id' => $sport->id,
        'name' => 'Falcons',
        'coach_name' => 'Coach A',
        'coach_email' => 'coach.a@example.test',
        'division' => 'A',
        'is_active' => true,
    ]);
    $teamAResponse->assertRedirect(route('tenant.teams.index'));

    $teamBResponse = $this->actingAs($user)->post(route('tenant.teams.store'), [
        'sport_id' => $sport->id,
        'name' => 'Wolves',
        'coach_name' => 'Coach B',
        'coach_email' => 'coach.b@example.test',
        'division' => 'A',
        'is_active' => true,
    ]);
    $teamBResponse->assertRedirect(route('tenant.teams.index'));

    $teamA = Team::query()->where('name', 'Falcons')->firstOrFail();
    $teamB = Team::query()->where('name', 'Wolves')->firstOrFail();

    $playerResponse = $this->actingAs($user)->post(route('tenant.players.store'), [
        'team_id' => $teamA->id,
        'student_id' => 'STU-1001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.test',
        'position' => 'Guard',
        'is_active' => true,
    ]);
    $playerResponse->assertRedirect(route('tenant.players.index'));

    $venueId = Venue::query()->firstOrFail()->id;

    $gameResponse = $this->actingAs($user)->post(route('tenant.games.store'), [
        'sport_id' => $sport->id,
        'home_team_id' => $teamA->id,
        'away_team_id' => $teamB->id,
        'venue_id' => $venueId,
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'status' => 'scheduled',
        'home_score' => null,
        'away_score' => null,
    ]);
    $gameResponse->assertRedirect(route('tenant.games.index'));

    $game = Game::query()->firstOrFail();

    $resultResponse = $this->actingAs($user)->patch(route('tenant.games.result', $game), [
        'status' => 'completed',
        'home_score' => 81,
        'away_score' => 76,
    ]);
    $resultResponse->assertRedirect(route('tenant.games.index'));

    $standingsPage = $this->actingAs($user)->get(route('tenant.standings.index'));
    $standingsPage->assertOk();
    $standingsPage->assertSee('Live Standings');
    $standingsPage->assertSee('Falcons');

    $standingsCsv = $this->actingAs($user)->get(route('tenant.pro.exports.standings.csv'));
    $standingsCsv->assertOk();

    $standingsPdf = $this->actingAs($user)->get(route('tenant.pro.exports.standings.pdf'));
    $standingsPdf->assertOk();

    $this->actingAs($user)->post(route('tenant.teams.store'), [
        'sport_id' => $sport->id,
        'name' => 'Tigers',
        'coach_name' => 'Coach C',
        'coach_email' => 'coach.c@example.test',
        'division' => 'A',
        'is_active' => true,
    ])->assertRedirect(route('tenant.teams.index'));

    $this->actingAs($user)->post(route('tenant.teams.store'), [
        'sport_id' => $sport->id,
        'name' => 'Bulls',
        'coach_name' => 'Coach D',
        'coach_email' => 'coach.d@example.test',
        'division' => 'A',
        'is_active' => true,
    ])->assertRedirect(route('tenant.teams.index'));

    $generateBracket = $this->actingAs($user)->post(route('tenant.pro.bracket.generate'), [
        'sport_id' => $sport->id,
    ]);
    $generateBracket->assertRedirect(route('tenant.pro.bracket', ['sport_id' => $sport->id]));

    $firstPlayable = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 1)
        ->whereNotNull('home_team_id')
        ->whereNotNull('away_team_id')
        ->firstOrFail();

    $winnerResponse = $this->actingAs($user)->patch(route('tenant.pro.bracket.matches.winner', $firstPlayable), [
        'winner_team_id' => $firstPlayable->home_team_id,
    ]);
    $winnerResponse->assertRedirect(route('tenant.pro.bracket', ['sport_id' => $sport->id]));

    $this->assertDatabaseHas('bracket_matches', [
        'id' => $firstPlayable->id,
        'winner_team_id' => $firstPlayable->home_team_id,
    ]);

    $this->assertDatabaseHas('bracket_match_audits', [
        'bracket_match_id' => $firstPlayable->id,
        'changed_by_user_id' => $user->id,
        'previous_winner_team_id' => null,
        'new_winner_team_id' => $firstPlayable->home_team_id,
    ]);

    $auditsCsv = $this->actingAs($user)->get(route('tenant.pro.exports.result-audits.csv'));
    $auditsCsv->assertOk();

    $auditsPdf = $this->actingAs($user)->get(route('tenant.pro.exports.result-audits.pdf'));
    $auditsPdf->assertOk();

    $resultAuditsPage = $this->actingAs($user)->get(route('tenant.audits.game-results.index'));
    $resultAuditsPage->assertOk();
    $resultAuditsPage->assertSee('Result Audit History');
});
