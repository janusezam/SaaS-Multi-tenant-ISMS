<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\BracketMatch;
use App\Models\Game;
use App\Models\GameResultAudit;
use App\Models\Sport;
use App\Models\Subscription;
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
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    foreach (['pro-tenant', 'basic-tenant', 'pro-exports', 'pro-student'] as $tenantId) {
        $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenantId.(string) config('tenancy.database.suffix', ''));

        if (is_file($databasePath)) {
            @unlink($databasePath);
        }
    }
});

function initializeTenantWithSchema(University $tenant): void
{
    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenant->id.(string) config('tenancy.database.suffix', ''));

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

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

    if (! Schema::hasTable('games')) {
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sport_id')->constrained('sports')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedBigInteger('venue_id')->nullable();
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

test('pro plan tenant can access analytics and bracket pages', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'pro-tenant',
        'name' => 'Pro University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $sport = Sport::query()->create([
        'name' => 'Bracket Sport',
        'code' => 'brkt',
        'description' => null,
        'is_active' => true,
    ]);

    foreach (['Seed 1', 'Seed 2', 'Seed 3', 'Seed 4', 'Seed 5'] as $teamName) {
        Team::query()->create([
            'sport_id' => $sport->id,
            'name' => $teamName,
            'coach_name' => null,
            'coach_email' => null,
            'division' => null,
            'is_active' => true,
        ]);
    }

    $analytics = $this->actingAs($user)->get(route('tenant.pro.analytics'));
    $analytics->assertOk();

    $bracket = $this->actingAs($user)->get(route('tenant.pro.bracket'));
    $bracket->assertOk();
    $bracket->assertSee('Quarterfinals');
    $bracket->assertSee('Semifinals');
    $bracket->assertSee('Final');
});

test('basic plan tenant cannot access pro pages', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'basic-tenant',
        'name' => 'Basic University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $response = $this->actingAs($user)->get(route('tenant.pro.analytics'));

    $response->assertRedirect(route('tenant.dashboard'));
    $response->assertSessionHas('upgrade_notice');
});

test('basic plan redirect to dashboard displays upgrade prompt', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'basic-upgrade-prompt',
        'name' => 'Basic Prompt University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $response = $this->actingAs($user)
        ->from(route('tenant.dashboard'))
        ->get(route('tenant.pro.analytics'));

    $response->assertRedirect(route('tenant.dashboard'));

    $dashboard = $this->actingAs($user)->get(route('tenant.dashboard'));
    $dashboard->assertOk();
    $dashboard->assertSee('Upgrade to Pro to access this feature.');
});

test('pro navigation visibility uses effective current plan from subscription', function () {
    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'pro-from-subscription',
        'name' => 'Subscription Pro University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    Subscription::query()->create([
        'tenant_id' => $tenant->id,
        'plan' => 'pro',
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addDays(15)->toDateString(),
    ]);

    initializeTenantWithSchema($tenant);

    $dashboard = $this->actingAs($user)->get(route('tenant.dashboard'));

    $dashboard->assertOk();
    $dashboard->assertSee(route('tenant.pro.analytics'), false);
    $dashboard->assertSee(route('tenant.pro.bracket'), false);
});

test('pro exports endpoints are available for pro tenant', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'pro-exports',
        'name' => 'Pro Exports University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $csv = $this->actingAs($user)->get(route('tenant.pro.exports.standings.csv'));
    $csv->assertOk();

    $pdf = $this->actingAs($user)->get(route('tenant.pro.exports.standings.pdf'));
    $pdf->assertOk();

    $sport = Sport::query()->create([
        'name' => 'Basketball',
        'code' => 'proaudits',
        'description' => null,
        'is_active' => true,
    ]);

    $homeTeam = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Pro A',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $awayTeam = Team::query()->create([
        'sport_id' => $sport->id,
        'name' => 'Pro B',
        'coach_name' => null,
        'coach_email' => null,
        'division' => null,
        'is_active' => true,
    ]);

    $game = Game::query()->create([
        'sport_id' => $sport->id,
        'home_team_id' => $homeTeam->id,
        'away_team_id' => $awayTeam->id,
        'venue_id' => null,
        'scheduled_at' => now()->addDay(),
        'status' => 'completed',
        'home_score' => 75,
        'away_score' => 70,
    ]);

    GameResultAudit::query()->create([
        'game_id' => $game->id,
        'changed_by_user_id' => $user->id,
        'previous_status' => 'scheduled',
        'new_status' => 'completed',
        'previous_home_score' => null,
        'new_home_score' => 75,
        'previous_away_score' => null,
        'new_away_score' => 70,
    ]);

    $auditsCsv = $this->actingAs($user)->get(route('tenant.pro.exports.result-audits.csv'));
    $auditsCsv->assertOk();

    $auditsPdf = $this->actingAs($user)->get(route('tenant.pro.exports.result-audits.pdf'));
    $auditsPdf->assertOk();
});

test('student player cannot access pro feature routes even on pro plan', function () {
    $user = User::factory()->create([
        'role' => 'student_player',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'pro-student',
        'name' => 'Pro Student University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $response = $this->actingAs($user)->get(route('tenant.pro.bracket'));

    $response->assertForbidden();
});

test('pro tenant can persist bracket and advance winner to next round', function () {
    $user = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'pro-tenant',
        'name' => 'Pro University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
    ]));

    initializeTenantWithSchema($tenant);

    $sport = Sport::query()->create([
        'name' => 'Knockout Sport',
        'code' => 'knock1',
        'description' => null,
        'is_active' => true,
    ]);

    foreach (['Alpha', 'Bravo', 'Charlie', 'Delta'] as $teamName) {
        Team::query()->create([
            'sport_id' => $sport->id,
            'name' => $teamName,
            'coach_name' => null,
            'coach_email' => null,
            'division' => null,
            'is_active' => true,
        ]);
    }

    $generate = $this->actingAs($user)->post(route('tenant.pro.bracket.generate'), [
        'sport_id' => $sport->id,
    ]);

    $generate->assertRedirect();
    $this->assertDatabaseCount('bracket_matches', 3);

    $firstMatch = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 1)
        ->whereNotNull('home_team_id')
        ->whereNotNull('away_team_id')
        ->firstOrFail();

    $submitWinner = $this->actingAs($user)->patch(route('tenant.pro.bracket.matches.winner', $firstMatch), [
        'winner_team_id' => $firstMatch->home_team_id,
    ]);

    $submitWinner->assertRedirect();

    $this->assertDatabaseHas('bracket_matches', [
        'id' => $firstMatch->id,
        'winner_team_id' => $firstMatch->home_team_id,
    ]);

    $final = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 2)
        ->where('match_number', 1)
        ->firstOrFail();

    expect(in_array($firstMatch->home_team_id, [$final->home_team_id, $final->away_team_id], true))->toBeTrue();

    $this->assertDatabaseHas('bracket_match_audits', [
        'bracket_match_id' => $firstMatch->id,
        'changed_by_user_id' => $user->id,
        'previous_winner_team_id' => null,
        'new_winner_team_id' => $firstMatch->home_team_id,
    ]);

    $auditsPage = $this->actingAs($user)->get(route('tenant.pro.bracket.audits', ['sport_id' => $sport->id]));
    $auditsPage->assertOk();
    $auditsPage->assertSee('Bracket Result Audits');
});
