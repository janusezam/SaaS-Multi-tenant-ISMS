<?php

use App\Models\University;
use Illuminate\Support\Facades\DB;

test('safe tenant migration command exits cleanly when no tenants match', function () {
    $this->artisan('app:run-tenant-migrations-safely --tenants=missing-tenant')
        ->assertExitCode(0);

    expect(DB::table('tenant_migration_runs')->count())->toBe(0);
});

test('safe tenant migration command tracks per-tenant results', function () {
    University::withoutEvents(function (): void {
        University::query()->create([
            'id' => 'tenant-a',
            'name' => 'Tenant A',
            'plan' => 'basic',
            'status' => 'active',
        ]);
    });

    $this->artisan('app:run-tenant-migrations-safely --tenants=tenant-a --stop-on-failure')
        ->assertExitCode(0);

    $run = DB::table('tenant_migration_runs')->latest('id')->first();

    expect($run)->not->toBeNull();
    expect($run->status)->toBe('completed');
    expect((int) $run->successful_tenant_count)->toBe(1);
    expect((int) $run->failed_tenant_count)->toBe(0);
    expect((int) $run->target_tenant_count)->toBe(1);

    $item = DB::table('tenant_migration_run_items')
        ->where('tenant_migration_run_id', $run->id)
        ->where('tenant_id', 'tenant-a')
        ->first();

    expect($item)->not->toBeNull();
    expect($item->status)->toBe('success');
    expect($item->finished_at)->not->toBeNull();
});
