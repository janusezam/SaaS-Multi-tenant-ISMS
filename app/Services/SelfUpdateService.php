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
}
