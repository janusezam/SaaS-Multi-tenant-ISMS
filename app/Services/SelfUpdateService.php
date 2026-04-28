<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class SelfUpdateService
{
    public function isUpdateInProgress(): bool
    {
        return Cache::store('central')->has('self_update.in_progress');
    }

    /**
     * @return string|null Error message if update cannot be started.
     */
    public function preflightError(): ?string
    {
        if (! app()->environment(['local', 'testing'])) {
            return 'Self-update is only available in local environments.';
        }

        if ($this->isUpdateInProgress()) {
            return 'An update is already in progress.';
        }

        $gitVersion = new Process(['git', '--version'], base_path());
        $gitVersion->run();

        if (! $gitVersion->isSuccessful()) {
            return 'Git is not available on this machine.';
        }

        $status = new Process(['git', 'status', '--porcelain'], base_path());
        $status->run();

        if (! $status->isSuccessful()) {
            return 'Unable to read git status.';
        }

        if (trim($status->getOutput()) !== '') {
            return 'Working tree is not clean. Commit/stash changes before updating.';
        }

        return null;
    }

    public function markInProgress(): void
    {
        Cache::store('central')->put('self_update.in_progress', true, now()->addHours(2));
    }

    /**
     * Launch the self-update command in a fully detached process.
     * On Windows, this creates a batch file and opens it in a new cmd.exe window.
     */
    public function runDetached(): void
    {
        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $base = base_path();
        $logFile = base_path('storage/logs/self-update.log');

        if (PHP_OS_FAMILY === 'Windows') {
            $batchFile = base_path('storage/app/self-update.bat');
            $storageDir = dirname($batchFile);

            if (! is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            // Write a batch file that runs the update, logs output, then deletes itself
            file_put_contents($batchFile, implode("\r\n", [
                '@echo off',
                'cd /d "'.$base.'"',
                '"'.$php.'" "'.$artisan.'" app:self-update > "'.$logFile.'" 2>&1',
                'del "%~f0"',
            ]));

            // Launch the batch file in its own minimized cmd.exe window
            // This is completely independent of Apache
            pclose(popen('start /min "" "'.$batchFile.'"', 'r'));
        } else {
            // On Linux/macOS, use nohup to detach
            $cmd = "nohup \"{$php}\" \"{$artisan}\" app:self-update > \"{$logFile}\" 2>&1 &";
            exec($cmd);
        }
    }
}
