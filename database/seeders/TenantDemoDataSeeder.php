<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TenantDemoDataSeeder extends Seeder
{
    protected int $facilitatorsCount = 3;

    protected int $coachesCount = 8;

    protected int $playersCount = 80;

    protected int $sportsCount = 4;

    protected int $teamsPerSportCount = 4;

    protected int $gamesPerSportCount = 6;

    protected int $seedValue = 2026;

    protected string $defaultPassword = 'password';

    protected bool $fresh = false;

    public function configure(array $options = []): self
    {
        $this->facilitatorsCount = max(1, (int) ($options['facilitators'] ?? $this->facilitatorsCount));
        $this->coachesCount = max(1, (int) ($options['coaches'] ?? $this->coachesCount));
        $this->playersCount = max(1, (int) ($options['players'] ?? $this->playersCount));
        $this->sportsCount = max(1, (int) ($options['sports'] ?? $this->sportsCount));
        $this->teamsPerSportCount = max(2, (int) ($options['teams_per_sport'] ?? $this->teamsPerSportCount));
        $this->gamesPerSportCount = max(1, (int) ($options['games_per_sport'] ?? $this->gamesPerSportCount));
        $this->seedValue = max(1, (int) ($options['seed'] ?? $this->seedValue));
        $this->defaultPassword = (string) ($options['password'] ?? $this->defaultPassword);
        $this->fresh = (bool) ($options['fresh'] ?? $this->fresh);

        return $this;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        fake()->seed($this->seedValue);

        if ($this->fresh) {
            $this->truncateTenantTables();
        }

        $tenantId = (string) (tenant()?->id ?? 'tenant');
        $emailSuffix = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tenantId) ?? 'tenant');
        $passwordHash = Hash::make($this->defaultPassword);

        $this->seedUsers($emailSuffix, $passwordHash);

        $sports = $this->seedSports();
        $venues = $this->seedVenues();
        $teams = $this->seedTeams($sports, $emailSuffix);

        $this->seedPlayers($teams, $emailSuffix);
        $this->seedGames($sports, $teams, $venues);
    }

    protected function truncateTenantTables(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'bracket_match_audits',
            'bracket_matches',
            'game_result_audits',
            'games',
            'players',
            'teams',
            'venues',
            'sports',
            'users',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    protected function seedUsers(string $emailSuffix, string $passwordHash): void
    {
        $tenant = tenant();
        $adminName = (string) ($tenant?->tenant_admin_name ?? 'University Admin');
        $adminEmail = (string) ($tenant?->tenant_admin_email ?? sprintf('admin@%s.test', $emailSuffix));
        $hasMustChangePassword = Schema::hasColumn('users', 'must_change_password');

        $baseAttributes = [
            'password' => $passwordHash,
            'email_verified_at' => now(),
        ];

        if ($hasMustChangePassword) {
            $baseAttributes['must_change_password'] = false;
        }

        User::query()->firstOrCreate(
            ['email' => $adminEmail],
            array_merge($baseAttributes, [
                'name' => $adminName,
                'role' => 'university_admin',
            ])
        );

        for ($index = 1; $index <= $this->facilitatorsCount; $index++) {
            User::query()->updateOrCreate(
                ['email' => sprintf('facilitator%02d@%s.test', $index, $emailSuffix)],
                [
                    'name' => sprintf('Facilitator %02d', $index),
                    'role' => 'sports_facilitator',
                ] + $baseAttributes
            );
        }

        for ($index = 1; $index <= $this->coachesCount; $index++) {
            User::query()->updateOrCreate(
                ['email' => sprintf('coach%02d@%s.test', $index, $emailSuffix)],
                [
                    'name' => sprintf('Coach User %02d', $index),
                    'role' => 'team_coach',
                ] + $baseAttributes
            );
        }

        for ($index = 1; $index <= $this->playersCount; $index++) {
            User::query()->updateOrCreate(
                ['email' => sprintf('player%03d@%s.test', $index, $emailSuffix)],
                [
                    'name' => sprintf('Player User %03d', $index),
                    'role' => 'student_player',
                ] + $baseAttributes
            );
        }
    }

    protected function seedSports(): Collection
    {
        $catalog = [
            ['name' => 'Basketball', 'code' => 'BASK'],
            ['name' => 'Volleyball', 'code' => 'VOLL'],
            ['name' => 'Football', 'code' => 'FOOT'],
            ['name' => 'Badminton', 'code' => 'BADM'],
            ['name' => 'Table Tennis', 'code' => 'TTEN'],
            ['name' => 'Futsal', 'code' => 'FUTS'],
            ['name' => 'Sepak Takraw', 'code' => 'SEPK'],
            ['name' => 'Esports', 'code' => 'ESPT'],
        ];

        $selected = collect($catalog)->take($this->sportsCount)->values();

        return $selected->map(function (array $sport, int $index): Sport {
            return Sport::query()->updateOrCreate(
                ['code' => $sport['code']],
                [
                    'name' => $sport['name'],
                    'description' => sprintf('Intramurals %d - Event %d', now()->year, $index + 1),
                    'is_active' => true,
                ]
            );
        });
    }

    protected function seedVenues(): Collection
    {
        return collect(range(1, max(3, $this->sportsCount)))->map(function (int $index): Venue {
            return Venue::query()->updateOrCreate(
                ['name' => sprintf('Venue %02d', $index)],
                [
                    'location' => sprintf('Campus Zone %d', $index),
                    'capacity' => fake()->numberBetween(120, 400),
                    'surface_type' => fake()->randomElement(['indoor', 'outdoor', 'hardcourt']),
                    'is_active' => true,
                ]
            );
        });
    }

    protected function seedTeams(Collection $sports, string $emailSuffix): Collection
    {
        $divisions = ['Junior', 'Senior', 'Men', 'Women', 'Open'];
        $coachCounter = 1;

        return $sports->flatMap(function (Sport $sport) use (&$coachCounter, $divisions, $emailSuffix): Collection {
            return collect(range(1, $this->teamsPerSportCount))->map(function (int $index) use ($sport, &$coachCounter, $divisions, $emailSuffix): Team {
                $teamName = sprintf('%s Team %02d', $sport->name, $index);

                $team = Team::query()->updateOrCreate(
                    [
                        'sport_id' => $sport->id,
                        'name' => $teamName,
                    ],
                    [
                        'coach_name' => sprintf('Coach %02d', $coachCounter),
                        'coach_email' => sprintf('coach%02d@%s.test', $coachCounter, $emailSuffix),
                        'division' => $divisions[($index - 1) % count($divisions)],
                        'is_active' => true,
                    ]
                );

                $coachCounter++;

                return $team;
            });
        })->values();
    }

    protected function seedPlayers(Collection $teams, string $emailSuffix): void
    {
        $teamsCount = max(1, $teams->count());
        $playersPerTeam = (int) ceil($this->playersCount / $teamsCount);
        $playerCounter = 1;

        foreach ($teams as $team) {
            foreach (range(1, $playersPerTeam) as $index) {
                if ($playerCounter > $this->playersCount) {
                    break 2;
                }

                Player::query()->updateOrCreate(
                    ['student_id' => sprintf('STU%05d', $playerCounter)],
                    [
                        'team_id' => $team->id,
                        'first_name' => sprintf('Player%03d', $playerCounter),
                        'last_name' => fake()->lastName(),
                        'email' => sprintf('player%03d@%s.test', $playerCounter, $emailSuffix),
                        'position' => fake()->randomElement(['Guard', 'Forward', 'Center', 'Captain', 'Sub']),
                        'is_active' => true,
                    ]
                );

                $playerCounter++;
            }
        }
    }

    protected function seedGames(Collection $sports, Collection $teams, Collection $venues): void
    {
        $statusPool = ['scheduled', 'scheduled', 'completed', 'cancelled'];

        foreach ($sports as $sport) {
            $sportTeams = $teams->where('sport_id', $sport->id)->values();

            if ($sportTeams->count() < 2) {
                continue;
            }

            foreach (range(1, $this->gamesPerSportCount) as $gameNumber) {
                $pair = $sportTeams->shuffle()->take(2)->values();

                $homeTeam = $pair->get(0);
                $awayTeam = $pair->get(1);

                if ($homeTeam === null || $awayTeam === null || $homeTeam->id === $awayTeam->id) {
                    continue;
                }

                $status = $statusPool[array_rand($statusPool)];
                $scheduledAt = Carbon::now()->addDays($gameNumber)->setTime(fake()->numberBetween(8, 18), fake()->randomElement([0, 15, 30, 45]));

                Game::query()->create([
                    'sport_id' => $sport->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'venue_id' => $venues->random()->id,
                    'scheduled_at' => $scheduledAt,
                    'status' => $status,
                    'home_score' => $status === 'completed' ? fake()->numberBetween(0, 99) : null,
                    'away_score' => $status === 'completed' ? fake()->numberBetween(0, 99) : null,
                ]);
            }
        }
    }
}
