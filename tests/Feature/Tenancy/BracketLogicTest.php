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
        'feature_flags' => ['bracket' => true],
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

function initializeTenant(University $tenant): void
{
    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenant->id.(string) config('tenancy.database.suffix', ''));

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    tenancy()->initialize($tenant);

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
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_score')->nullable();
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

test('3-team bracket does not advance first-round winner prematurely to champion', function () {
    $user = User::factory()->create(['role' => 'university_admin']);
    $tenantId = 'bracket-test-'.rand(1000, 9999);
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Bracket Test',
        'plan' => 'pro',
        'status' => 'active',
    ]));

    initializeTenant($tenant);

    $sport = Sport::query()->updateOrCreate(['code' => 'box'], ['name' => 'Boxing']);
    
    // Create 3 teams
    $team1 = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Team 1']);
    $team2 = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Team 2']);
    $team3 = Team::query()->create(['sport_id' => $sport->id, 'name' => 'Team 3']);

    // Generate bracket
    // A 3-team bracket size is 4.
    // Participants: [Team 1, Team 2, Team 3, BYE]
    // R1 M1: Team 1 vs BYE -> Team 1 advances to R2 M1
    // R1 M2: Team 2 vs Team 3 -> Winner advances to R2 M1
    // R2 M1 (Final): Team 1 vs Winner of Match 2
    
    $response = $this->actingAs($user)->post(route('tenant.pro.bracket.generate'), [
        'sport_id' => $sport->id,
    ]);

    $response->assertRedirect();
    
    if (session('errors')) {
        dd(session('errors')->all());
    }

    // Check R1 M1 (The BYE match)
    $match1 = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 1)
        ->where('match_number', 1)
        ->firstOrFail();

    // The BYE should have already advanced Team 1 to the final (R2 M1)
    // because autoAdvanceByes is called at generation.
    expect($match1->winner_team_id)->toBe($team1->id);

    $final = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 2)
        ->where('match_number', 1)
        ->firstOrFail();

    // Team 1 should be in the final
    expect($final->home_team_id)->toBe($team1->id);
    // But the final should NOT have a winner yet, even though Team 1 has no opponent yet.
    expect($final->winner_team_id)->toBeNull();
    expect($final->away_slot_label)->toBe('Winner of Match 2');

    // Now record winner for Match 2
    $match2 = BracketMatch::query()
        ->where('sport_id', $sport->id)
        ->where('round_number', 1)
        ->where('match_number', 2)
        ->firstOrFail();

    $this->actingAs($user)->patch(route('tenant.pro.bracket.matches.winner', $match2), [
        'winner_team_id' => $team2->id,
    ]);

    // Now Match 2 should have a winner
    expect($match2->fresh()->winner_team_id)->toBe($team2->id);

    // And the Final should now have both teams
    $final->refresh();
    expect($final->home_team_id)->toBe($team1->id);
    expect($final->away_team_id)->toBe($team2->id);
    expect($final->away_slot_label)->toBeNull();
    
    // AND THE FINAL SHOULD STILL HAVE NO WINNER (Wait for user to set it)
    expect($final->winner_team_id)->toBeNull();
});
