<?php

declare(strict_types=1);

use App\Models\University;
use Illuminate\Support\Facades\Cache;

function tenantCacheCompatibilityDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'tenant-cache-compatibility-test'.(string) config('tenancy.database.suffix');

    return database_path($databaseName);
}

function initializeTenantCacheCompatibilityContext(): University
{
    $databasePath = tenantCacheCompatibilityDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'tenant-cache-compatibility-test',
        'name' => 'Tenant Cache Compatibility University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
        'data' => [],
    ]));

    tenancy()->initialize($tenant);

    return $tenant;
}

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    $databasePath = tenantCacheCompatibilityDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

test('tenant cache works when cache store does not support tagging', function () {
    config(['cache.default' => 'file']);

    initializeTenantCacheCompatibilityContext();

    Cache::put('compat.key', 'ok', now()->addMinutes(5));

    expect(Cache::get('compat.key'))->toBe('ok');
});
