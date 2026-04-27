<?php

namespace App\Console\Commands;

use App\Models\SystemUpdate;
use Illuminate\Console\Command;

class SyncSystemUpdatesFromAppVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-system-updates-from-app-version
                            {--release-version= : Version value to publish (defaults to config app.version)}
                            {--summary= : Optional release summary text}
                            {--force : Publish even if this version already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the current app version into tenant-facing system updates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = trim((string) ($this->option('release-version') ?: config('app.version', 'v1.0.0')));

        if ($version === '') {
            $this->error('No version provided. Set APP_VERSION or pass --release-version=');

            return self::FAILURE;
        }

        $existing = SystemUpdate::query()
            ->where('version', $version)
            ->first();

        if ($existing !== null && ! (bool) $this->option('force')) {
            $this->line("System update for {$version} already exists (id: {$existing->id}).");

            return self::SUCCESS;
        }

        $summary = trim((string) ($this->option('summary') ?: 'Platform release synced from application version.'));

        $update = SystemUpdate::query()->create([
            'title' => "Release {$version}",
            'summary' => $summary,
            'version' => $version,
            'source' => 'github',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->info("Published system update id {$update->id} for {$version}.");

        return self::SUCCESS;
    }
}
