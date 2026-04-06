<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CaptureTenantRuntimeMetrics
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant() !== null) {
            $request->attributes->set('tenant_metrics_started_at', microtime(true));
            $request->attributes->set('tenant_metrics_db_enabled', true);
            DB::connection()->enableQueryLog();
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $tenant = tenant();

        if ($tenant === null) {
            return;
        }

        $startedAt = (float) $request->attributes->get('tenant_metrics_started_at', microtime(true));
        $responseTimeMs = (int) max(1, round((microtime(true) - $startedAt) * 1000));

        $dbTimeMs = 0;

        if ($request->attributes->get('tenant_metrics_db_enabled') === true) {
            $queryLog = DB::connection()->getQueryLog();
            $dbTimeMs = (int) round(collect($queryLog)->sum(static fn (array $query): float => (float) ($query['time'] ?? 0)));
            DB::connection()->flushQueryLog();
        }

        $memoryPeakMb = (int) max(1, round(memory_get_peak_usage(true) / 1024 / 1024));
        $centralConnection = (string) config('tenancy.database.central_connection', config('database.default'));

        if (! Schema::connection($centralConnection)->hasTable('tenant_runtime_metrics')) {
            return;
        }

        DB::connection($centralConnection)->table('tenant_runtime_metrics')->insert([
            'tenant_id' => (string) $tenant->getTenantKey(),
            'route_name' => $request->route()?->getName(),
            'request_path' => substr('/'.ltrim($request->path(), '/'), 0, 255),
            'request_method' => strtoupper($request->method()),
            'status_code' => (int) $response->getStatusCode(),
            'response_time_ms' => $responseTimeMs,
            'db_time_ms' => $dbTimeMs,
            'memory_peak_mb' => $memoryPeakMb,
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
