<?php

use App\Models\SuperAdmin;
use App\Models\University;
use Illuminate\Support\Facades\DB;

test('tenant monitoring requires super admin authentication', function () {
    $response = $this->get(route('central.tenant-monitoring.index'));

    $response->assertRedirect(route('central.login'));
});

test('tenant monitoring data endpoint requires super admin authentication', function () {
    $response = $this->get(route('central.tenant-monitoring.data'));

    $response->assertRedirect(route('central.login'));
});

test('authenticated super admin can view tenant monitoring dashboard', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Monitor',
        'email' => 'central-monitor@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.tenant-monitoring.index'));

    $response->assertOk();
    $response->assertSee('Tenant Monitoring');
    $response->assertSee('Avg CPU');
    $response->assertSee('Avg Memory');
    $response->assertSee('Avg DB Load');
});

test('authenticated super admin can fetch tenant monitoring data payload', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Monitor Data',
        'email' => 'central-monitor-data@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.tenant-monitoring.data'));

    $response->assertOk();
    $response->assertJsonStructure([
        'as_of',
        'selected_tenant_id',
        'selected_range_minutes',
        'range_options',
        'tenants',
        'summary' => [
            'tenant_count',
            'avg_cpu',
            'avg_memory',
            'avg_db_load',
            'total_requests_per_minute',
            'top_anomaly_tenant',
        ],
        'tenant_rows',
        'anomaly_rankings',
        'highlighted_tenant_ids',
        'endpoint_breakdown',
        'charts' => [
            'labels',
            'requests_per_minute_series',
            'cpu_series',
            'bar_comparison',
        ],
    ]);
});

test('tenant monitoring can be filtered to a single tenant', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Monitor Filter',
        'email' => 'central-monitor-filter@example.test',
        'password' => 'password',
    ]);

    $tenantAId = 'tenant-monitor-a-'.uniqid();
    $tenantA = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantAId,
        'name' => 'Tenant Monitor A',
        'tenant_admin_name' => 'Tenant A Admin',
        'tenant_admin_email' => 'tenant-monitor-a@example.test',
        'status' => 'active',
        'plan' => 'basic',
        'expires_at' => now()->addMonth(),
    ]));

    $tenantBId = 'tenant-monitor-b-'.uniqid();
    $tenantB = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantBId,
        'name' => 'Tenant Monitor B',
        'tenant_admin_name' => 'Tenant B Admin',
        'tenant_admin_email' => 'tenant-monitor-b@example.test',
        'status' => 'active',
        'plan' => 'pro',
        'expires_at' => now()->addYear(),
    ]));

    $centralConnection = (string) config('tenancy.database.central_connection', config('database.default'));

    DB::connection($centralConnection)->table('tenant_runtime_metrics')->insert([
        [
            'tenant_id' => $tenantA->id,
            'route_name' => 'tenant.dashboard',
            'request_path' => '/app/dashboard',
            'request_method' => 'GET',
            'status_code' => 200,
            'response_time_ms' => 120,
            'db_time_ms' => 15,
            'memory_peak_mb' => 28,
            'recorded_at' => now()->subMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tenant_id' => $tenantB->id,
            'route_name' => 'tenant.dashboard',
            'request_path' => '/app/dashboard',
            'request_method' => 'GET',
            'status_code' => 200,
            'response_time_ms' => 90,
            'db_time_ms' => 10,
            'memory_peak_mb' => 24,
            'recorded_at' => now()->subMinute(),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.tenant-monitoring.data', ['tenant_id' => $tenantB->id]));

    $response->assertOk();
    $response->assertJsonPath('selected_tenant_id', $tenantB->id);
    $response->assertJsonPath('tenant_rows.0.id', $tenantB->id);
    $response->assertJsonPath('charts.requests_per_minute_series.0.label', $tenantB->name);
    $response->assertJsonPath('endpoint_breakdown.0.request_path', '/app/dashboard');
    expect($response->json('tenant_rows'))->toHaveCount(1);
});

test('tenant monitoring range filter excludes metrics outside selected window', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Monitor Range',
        'email' => 'central-monitor-range@example.test',
        'password' => 'password',
    ]);

    $tenantId = 'tenant-monitor-range-'.uniqid();
    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => $tenantId,
        'name' => 'Tenant Monitor Range',
        'tenant_admin_name' => 'Tenant Range Admin',
        'tenant_admin_email' => 'tenant-monitor-range@example.test',
        'status' => 'active',
        'plan' => 'basic',
        'expires_at' => now()->addMonth(),
    ]));

    $centralConnection = (string) config('tenancy.database.central_connection', config('database.default'));

    DB::connection($centralConnection)->table('tenant_runtime_metrics')->insert([
        [
            'tenant_id' => $tenant->id,
            'route_name' => 'tenant.schedules.index',
            'request_path' => '/app/schedules',
            'request_method' => 'GET',
            'status_code' => 200,
            'response_time_ms' => 110,
            'db_time_ms' => 18,
            'memory_peak_mb' => 29,
            'recorded_at' => now()->subMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'tenant_id' => $tenant->id,
            'route_name' => 'tenant.archived.index',
            'request_path' => '/app/archived',
            'request_method' => 'GET',
            'status_code' => 500,
            'response_time_ms' => 330,
            'db_time_ms' => 40,
            'memory_peak_mb' => 33,
            'recorded_at' => now()->subMinutes(40),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.tenant-monitoring.data', [
            'tenant_id' => $tenant->id,
            'range_minutes' => 15,
        ]));

    $response->assertOk();
    $response->assertJsonPath('selected_range_minutes', 15);
    $response->assertJsonPath('endpoint_breakdown.0.request_path', '/app/schedules');

    $endpointPaths = collect($response->json('endpoint_breakdown'))->pluck('request_path')->all();
    expect($endpointPaths)->not->toContain('/app/archived');
});
