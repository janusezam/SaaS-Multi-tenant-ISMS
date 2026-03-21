<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
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
        CheckRole::class,
    ]);
});

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    $databasePath = database_path('tenantpolicy-tenant');

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializePolicyTenant(): void
{
    $databasePath = database_path('tenantpolicy-tenant');

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'policy-tenant',
        'name' => 'Policy University',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addDays(15),
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
        });
    }
}

test('policy blocks student from pro routes even without role middleware', function () {
    $student = User::factory()->create([
        'role' => 'student_player',
    ]);

    initializePolicyTenant();

    $response = $this->actingAs($student)->get(route('tenant.pro.analytics'));

    $response->assertForbidden();
});

test('policy allows facilitator on pro routes when role middleware is bypassed', function () {
    $facilitator = User::factory()->create([
        'role' => 'sports_facilitator',
    ]);

    initializePolicyTenant();

    $response = $this->actingAs($facilitator)->get(route('tenant.pro.analytics'));

    $response->assertOk();
});
