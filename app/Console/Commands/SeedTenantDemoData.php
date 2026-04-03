<?php

namespace App\Console\Commands;

use App\Models\University;
use Database\Seeders\TenantDemoDataSeeder;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;

class SeedTenantDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isms:seed-tenant-demo
                            {tenant : Tenant ID or tenant domain (e.g. dorsu or dorsu.isms.test)}
                            {--fresh : Clear existing tenant sports/teams/players/games/users before seeding}
                            {--facilitators=3 : Number of facilitator users}
                            {--coaches=8 : Number of coach users}
                            {--players=80 : Number of player users}
                            {--sports=4 : Number of sports/events}
                            {--teams-per-sport=4 : Number of teams per sport}
                            {--games-per-sport=6 : Number of games per sport}
                            {--seed=2026 : Faker seed for deterministic demo data}
                            {--password=password : Default password for generated tenant users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed QA/demo tenant data (roles, sports, teams, players, and games) for a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantInput = (string) $this->argument('tenant');

        $tenant = $this->resolveTenant($tenantInput);

        if ($tenant === null) {
            $this->error("Tenant not found for input: {$tenantInput}");

            return self::FAILURE;
        }

        $tenant->loadMissing('domains');

        $this->info(sprintf('Seeding demo data for tenant: %s (%s)', $tenant->name, $tenant->id));

        tenancy()->initialize($tenant);

        try {
            app(TenantDemoDataSeeder::class)
                ->configure([
                    'fresh' => (bool) $this->option('fresh'),
                    'facilitators' => (int) $this->option('facilitators'),
                    'coaches' => (int) $this->option('coaches'),
                    'players' => (int) $this->option('players'),
                    'sports' => (int) $this->option('sports'),
                    'teams_per_sport' => (int) $this->option('teams-per-sport'),
                    'games_per_sport' => (int) $this->option('games-per-sport'),
                    'seed' => (int) $this->option('seed'),
                    'password' => (string) $this->option('password'),
                ])
                ->run();
        } finally {
            tenancy()->end();
        }

        $this->newLine();
        $this->info('Tenant demo data seeded successfully.');
        $this->line(sprintf('Tenant URL: http://%s/app/login', $tenant->domains->first()?->domain ?? 'unknown-domain'));
        $this->line(sprintf('Suggested admin email: %s', $tenant->tenant_admin_email ?? 'admin@'.$tenant->id.'.test'));
        $this->line('Generated roles: university_admin, sports_facilitator, team_coach, student_player');
        $this->line(sprintf('Default password: %s', (string) $this->option('password')));

        return self::SUCCESS;
    }

    protected function resolveTenant(string $tenantInput): ?University
    {
        $tenantById = University::query()->where('id', $tenantInput)->first();

        if ($tenantById !== null) {
            return $tenantById;
        }

        $domain = Domain::query()->where('domain', $tenantInput)->first();

        if ($domain === null) {
            return null;
        }

        return University::query()->where('id', $domain->tenant_id)->first();
    }
}
