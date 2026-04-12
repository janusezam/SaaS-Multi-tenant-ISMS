<?php

namespace App\Console\Commands;

use App\Models\University;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class RunTenantMigrationsSafely extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-tenant-migrations-safely
                            {--tenants=* : Specific tenant IDs to migrate}
                            {--pretend : Show SQL only, do not execute migration statements}
                            {--stop-on-failure : Stop processing tenants when one fails}
                            {--rollback-on-failure : Roll back already migrated tenants if any failure happens}
                            {--rollback-step=1 : Number of migration steps to roll back per tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant migrations with central tracking, failure visibility, and optional rollback';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantIds = collect((array) $this->option('tenants'))
            ->map(fn ($id): string => trim((string) $id))
            ->filter()
            ->values();

        $tenants = University::query()
            ->when($tenantIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $tenantIds->all()))
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($tenants->isEmpty()) {
            $this->warn('No tenants matched the requested scope.');

            return self::SUCCESS;
        }

        $pretend = (bool) $this->option('pretend');
        $stopOnFailure = (bool) $this->option('stop-on-failure');
        $rollbackOnFailure = (bool) $this->option('rollback-on-failure');
        $rollbackStep = max(1, (int) $this->option('rollback-step'));

        $runId = DB::table('tenant_migration_runs')->insertGetId([
            'status' => 'running',
            'triggered_by' => 'console',
            'options' => json_encode([
                'tenant_ids' => $tenantIds->all(),
                'pretend' => $pretend,
                'stop_on_failure' => $stopOnFailure,
                'rollback_on_failure' => $rollbackOnFailure,
                'rollback_step' => $rollbackStep,
            ], JSON_THROW_ON_ERROR),
            'target_tenant_count' => $tenants->count(),
            'successful_tenant_count' => 0,
            'failed_tenant_count' => 0,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Started tenant migration run #{$runId} for {$tenants->count()} tenant(s).");

        $successfulTenantIds = [];
        $failed = 0;

        foreach ($tenants as $tenant) {
            $itemId = DB::table('tenant_migration_run_items')->insertGetId([
                'tenant_migration_run_id' => $runId,
                'tenant_id' => (string) $tenant->id,
                'tenant_name' => (string) $tenant->name,
                'status' => 'running',
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                $exitCode = Artisan::call('tenants:migrate', $this->buildMigrateArguments((string) $tenant->id, $pretend));
                $output = trim(Artisan::output());

                if ($exitCode !== 0) {
                    throw new \RuntimeException($output !== '' ? $output : 'Unknown tenant migration failure.');
                }

                DB::table('tenant_migration_run_items')
                    ->where('id', $itemId)
                    ->update([
                        'status' => 'success',
                        'migration_output' => $output,
                        'finished_at' => now(),
                        'updated_at' => now(),
                    ]);

                $successfulTenantIds[] = (string) $tenant->id;
                $this->line("[OK] Migrated tenant {$tenant->id}");
            } catch (Throwable $exception) {
                $failed++;

                DB::table('tenant_migration_run_items')
                    ->where('id', $itemId)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                        'finished_at' => now(),
                        'updated_at' => now(),
                    ]);

                $this->error("[FAILED] Tenant {$tenant->id}: {$exception->getMessage()}");

                if ($stopOnFailure) {
                    break;
                }
            }
        }

        if ($failed > 0 && $rollbackOnFailure && $successfulTenantIds !== []) {
            $this->warn('Failure detected. Attempting rollback on successfully migrated tenants...');

            foreach ($successfulTenantIds as $tenantId) {
                $exitCode = Artisan::call('tenants:rollback', $this->buildRollbackArguments($tenantId, $rollbackStep, $pretend));
                $rollbackOutput = trim(Artisan::output());

                DB::table('tenant_migration_run_items')
                    ->where('tenant_migration_run_id', $runId)
                    ->where('tenant_id', $tenantId)
                    ->update([
                        'status' => $exitCode === 0 ? 'rolled_back' : 'rollback_failed',
                        'rollback_output' => $rollbackOutput,
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table('tenant_migration_runs')
            ->where('id', $runId)
            ->update([
                'status' => $failed > 0 ? 'failed' : 'completed',
                'successful_tenant_count' => count($successfulTenantIds),
                'failed_tenant_count' => $failed,
                'finished_at' => now(),
                'updated_at' => now(),
            ]);

        $this->newLine();
        $this->info("Run #{$runId} finished. Success: ".count($successfulTenantIds).", Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMigrateArguments(string $tenantId, bool $pretend): array
    {
        return [
            '--tenants' => [$tenantId],
            '--force' => true,
            '--path' => (array) config('tenancy.migration_parameters.--path', []),
            '--realpath' => (bool) config('tenancy.migration_parameters.--realpath', true),
            '--pretend' => $pretend,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildRollbackArguments(string $tenantId, int $rollbackStep, bool $pretend): array
    {
        return [
            '--tenants' => [$tenantId],
            '--step' => $rollbackStep,
            '--force' => true,
            '--path' => (array) config('tenancy.migration_parameters.--path', []),
            '--realpath' => (bool) config('tenancy.migration_parameters.--realpath', true),
            '--pretend' => $pretend,
        ];
    }
}
