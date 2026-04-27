<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\EnvFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class SelfUpdateCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:self-update';

    /**
     * @var string
     */
    protected $description = 'Update this local install (git pull + composer + npm build + migrate).';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Self-update is only supported in local environments.');

            return self::FAILURE;
        }

        $lock = Cache::lock('self_update.lock', 60 * 60 * 2);

        if (! $lock->get()) {
            $this->warn('An update is already in progress.');

            return self::SUCCESS;
        }

        try {
            Cache::store('central')->put('self_update.in_progress', true, now()->addHours(2));

            $this->runStep(['git', 'fetch', 'origin', 'main'], 'Fetching origin/main...');
            $this->runStep(['git', 'fetch', '--prune', '--tags', 'origin'], 'Fetching origin tags...');
            $this->runStep(['git', 'checkout', 'main'], 'Checking out main...');
            $this->runStep(['git', 'pull', '--ff-only', 'origin', 'main'], 'Pulling latest main...');

            $this->runStep([
                'composer',
                'install',
                '--no-interaction',
                '--prefer-dist',
                '--no-progress',
            ], 'Running composer install...');

            $this->runStep(['npm', 'install'], 'Running npm install...');
            $this->runStep(['npm', 'run', 'build'], 'Running npm run build...');

            $this->runStep([
                PHP_BINARY,
                base_path('artisan'),
                'migrate',
                '--force',
                '--no-interaction',
            ], 'Running migrations...');


            $this->tryUpdateLocalAppVersion();

            $this->runStep([
                PHP_BINARY,
                base_path('artisan'),
                'optimize:clear',
            ], 'Clearing caches (final)...');


            $this->info('Self-update completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Self-update failed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            Cache::store('central')->forget('self_update.in_progress');
            optional($lock)->release();
        }
    }

    /**
     * @param  array<int, string>  $command
     */
    private function runStep(array $command, string $label): void
    {
        $this->line($label);

        $process = new Process($command, base_path());
        $process->setTimeout(null);
        $process->run(function (string $type, string $buffer): void {
            $output = trim($buffer);

            if ($output === '') {
                return;
            }

            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) !== ''
                ? trim($process->getErrorOutput())
                : 'Command failed: '.implode(' ', $command));
        }
    }

    private function tryUpdateLocalAppVersion(): void
    {
        $tag = $this->latestGitTag();

        if ($tag === null) {
            return;
        }

        try {
            app(EnvFile::class)->setKey(app()->environmentFilePath(), 'APP_VERSION', $tag);
            $this->info("Updated APP_VERSION to {$tag}.");
        } catch (\Throwable $exception) {
            $this->warn('Unable to update APP_VERSION automatically: '.$exception->getMessage());
        }
    }

    private function latestGitTag(): ?string
    {
        $process = new Process(['git', 'describe', '--tags', '--abbrev=0'], base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $tag = trim($process->getOutput());

        if ($tag === '') {
            return null;
        }

        return str_starts_with($tag, 'v') ? $tag : 'v'.$tag;
    }
}
