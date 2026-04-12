<?php

use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Artisan;

test('app version sync command publishes a system update', function () {
    config()->set('app.version', 'v9.9.9');

    Artisan::call('app:sync-system-updates-from-app-version');

    $this->assertDatabaseHas('system_updates', [
        'title' => 'Release v9.9.9',
        'version' => 'v9.9.9',
        'is_published' => true,
    ]);
});

test('app version sync command is idempotent by default', function () {
    config()->set('app.version', 'v9.9.8');

    Artisan::call('app:sync-system-updates-from-app-version');
    Artisan::call('app:sync-system-updates-from-app-version');

    expect(SystemUpdate::query()->where('version', 'v9.9.8')->count())->toBe(1);
});
