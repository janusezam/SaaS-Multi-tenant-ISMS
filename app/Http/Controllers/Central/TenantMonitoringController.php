<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\University;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $selectedTenantId = $this->normalizeTenantId((string) $request->query('tenant_id', ''));
        $rangeMinutes = $this->normalizeRangeMinutes((string) $request->query('range_minutes', ''));
        $payload = $this->buildMonitoringPayload($selectedTenantId, $rangeMinutes);

        return view('central.monitoring.index', [
            'monitoringPayload' => $payload,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $selectedTenantId = $this->normalizeTenantId((string) $request->query('tenant_id', ''));
        $rangeMinutes = $this->normalizeRangeMinutes((string) $request->query('range_minutes', ''));

        return response()->json($this->buildMonitoringPayload($selectedTenantId, $rangeMinutes));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildMonitoringPayload(?string $selectedTenantId = null, int $rangeMinutes = 30): array
    {
        $now = now();
        $windowMinutes = max(1, $rangeMinutes);
        $start = $now->copy()->subMinutes($windowMinutes - 1)->startOfMinute();
        $centralConnection = (string) config('tenancy.database.central_connection', config('database.default'));
        $memoryLimitMb = $this->memoryLimitMb();

        $tenants = University::query()
            ->with('subscription')
            ->orderBy('name')
            ->get()
            ->keyBy(fn (University $tenant): string => (string) $tenant->id);

        $metrics = DB::connection($centralConnection)
            ->table('tenant_runtime_metrics')
            ->where('recorded_at', '>=', $start)
            ->orderBy('recorded_at')
            ->get();

        /** @var Collection<string, Collection<int, object>> $metricsByTenant */
        $metricsByTenant = $metrics->groupBy(fn (object $row): string => (string) $row->tenant_id);
        $minuteLabels = $this->minuteLabels($start, $now);

        $tenantRows = $tenants
            ->map(function (University $tenant) use ($metricsByTenant, $memoryLimitMb, $windowMinutes): array {
                $tenantId = (string) $tenant->id;
                $rows = $metricsByTenant->get($tenantId, collect());

                $requestCount = $rows->count();
                $avgResponseMs = (float) ($rows->avg('response_time_ms') ?? 0);
                $avgDbMs = (float) ($rows->avg('db_time_ms') ?? 0);
                $peakMemoryMb = (float) ($rows->max('memory_peak_mb') ?? 0);
                $errorRatePercent = $requestCount > 0
                    ? round(((int) $rows->filter(fn (object $row): bool => (int) $row->status_code >= 500)->count() / $requestCount) * 100, 1)
                    : 0.0;

                $requestsPerMinute = $requestCount > 0 ? round($requestCount / $windowMinutes, 1) : 0.0;
                $cpuPercent = $this->toPercent(($avgResponseMs / 12) + ($requestsPerMinute * 1.8));
                $memoryPercent = $this->toPercent(($peakMemoryMb / max(1, $memoryLimitMb)) * 100);
                $dbLoadPercent = $this->toPercent(($avgDbMs * 2.4) + ($requestsPerMinute * 0.9));
                $responsePressurePercent = $this->toPercent($avgResponseMs / 10);
                $anomalyScore = round(
                    ($errorRatePercent * 0.45)
                    + ($cpuPercent * 0.25)
                    + ($dbLoadPercent * 0.15)
                    + ($responsePressurePercent * 0.10)
                    + ($memoryPercent * 0.05),
                    1,
                );

                return [
                    'id' => $tenantId,
                    'name' => (string) $tenant->name,
                    'status' => $tenant->currentStatus(),
                    'plan' => $tenant->currentPlan(),
                    'requests_per_minute' => $requestsPerMinute,
                    'avg_response_ms' => round($avgResponseMs, 1),
                    'avg_db_ms' => round($avgDbMs, 1),
                    'cpu_percent' => $cpuPercent,
                    'memory_percent' => $memoryPercent,
                    'db_load_percent' => $dbLoadPercent,
                    'error_rate_percent' => $errorRatePercent,
                    'anomaly_score' => $anomalyScore,
                ];
            })
            ->sortByDesc('requests_per_minute')
            ->values();

        $selectedTenantExists = $selectedTenantId !== null
            ? $tenants->has($selectedTenantId)
            : false;
        $effectiveSelectedTenantId = $selectedTenantExists ? $selectedTenantId : null;

        $chartTenantIds = $effectiveSelectedTenantId !== null
            ? [$effectiveSelectedTenantId]
            : $tenantRows->take(5)->pluck('id')->all();
        $rpmSeries = [];
        $cpuSeries = [];

        foreach ($chartTenantIds as $tenantId) {
            $tenant = $tenants->get((string) $tenantId);
            $rows = $metricsByTenant->get((string) $tenantId, collect())
                ->groupBy(function (object $row): string {
                    return Carbon::parse((string) $row->recorded_at)->format('H:i');
                });

            $rpmPoints = [];
            $cpuPoints = [];

            foreach ($minuteLabels as $label) {
                $bucketRows = $rows->get($label, collect());
                $count = $bucketRows->count();
                $avgResponseMs = (float) ($bucketRows->avg('response_time_ms') ?? 0);

                $rpmPoints[] = $count;
                $cpuPoints[] = $this->toPercent(($avgResponseMs / 10) + ($count * 2));
            }

            $name = (string) ($tenant?->name ?? $tenantId);

            $rpmSeries[] = [
                'label' => $name,
                'data' => $rpmPoints,
            ];

            $cpuSeries[] = [
                'label' => $name,
                'data' => $cpuPoints,
            ];
        }

        $displayTenantRows = $effectiveSelectedTenantId !== null
            ? $tenantRows
                ->where('id', $effectiveSelectedTenantId)
                ->values()
            : $tenantRows->take(15)->values();

        $anomalyRankings = $tenantRows
            ->sortByDesc('anomaly_score')
            ->take(5)
            ->values();

        $highlightedTenantIds = $anomalyRankings
            ->take(3)
            ->pluck('id')
            ->all();

        if ($effectiveSelectedTenantId !== null && ! in_array($effectiveSelectedTenantId, $highlightedTenantIds, true)) {
            $highlightedTenantIds[] = $effectiveSelectedTenantId;
        }

        $endpointBreakdown = $effectiveSelectedTenantId === null
            ? collect()
            : $metricsByTenant
                ->get($effectiveSelectedTenantId, collect())
                ->groupBy(fn (object $row): string => (string) ($row->request_path ?? '/'))
                ->map(function (Collection $rows, string $path): array {
                    $requestCount = $rows->count();

                    return [
                        'request_path' => $path,
                        'route_name' => (string) ($rows->first()->route_name ?? ''),
                        'request_count' => $requestCount,
                        'avg_response_ms' => round((float) ($rows->avg('response_time_ms') ?? 0), 1),
                        'avg_db_ms' => round((float) ($rows->avg('db_time_ms') ?? 0), 1),
                        'error_rate_percent' => $requestCount > 0
                            ? round(((int) $rows->filter(fn (object $row): bool => (int) $row->status_code >= 500)->count() / $requestCount) * 100, 1)
                            : 0.0,
                    ];
                })
                ->sortByDesc('request_count')
                ->take(10)
                ->values();

        return [
            'as_of' => $now->toDateTimeString(),
            'selected_tenant_id' => $effectiveSelectedTenantId,
            'selected_range_minutes' => $windowMinutes,
            'range_options' => $this->rangeOptions(),
            'selected_tenant' => $effectiveSelectedTenantId === null
                ? null
                : $displayTenantRows->first(),
            'tenants' => $tenants
                ->map(fn (University $tenant): array => [
                    'id' => (string) $tenant->id,
                    'name' => (string) $tenant->name,
                ])
                ->sortBy('name')
                ->values(),
            'summary' => [
                'tenant_count' => $tenantRows->count(),
                'avg_cpu' => (int) round($tenantRows->avg('cpu_percent') ?? 0),
                'avg_memory' => (int) round($tenantRows->avg('memory_percent') ?? 0),
                'avg_db_load' => (int) round($tenantRows->avg('db_load_percent') ?? 0),
                'total_requests_per_minute' => (float) round($tenantRows->sum('requests_per_minute') ?? 0, 1),
                'top_cpu_tenant' => $tenantRows->sortByDesc('cpu_percent')->first(),
                'top_anomaly_tenant' => $anomalyRankings->first(),
            ],
            'tenant_rows' => $displayTenantRows,
            'anomaly_rankings' => $anomalyRankings,
            'highlighted_tenant_ids' => $highlightedTenantIds,
            'endpoint_breakdown' => $endpointBreakdown,
            'charts' => [
                'labels' => $minuteLabels,
                'requests_per_minute_series' => $rpmSeries,
                'cpu_series' => $cpuSeries,
                'bar_comparison' => $tenantRows
                    ->when(
                        $effectiveSelectedTenantId !== null,
                        fn (Collection $rows): Collection => $rows->where('id', $effectiveSelectedTenantId),
                        fn (Collection $rows): Collection => $rows->take(10),
                    )
                    ->map(fn (array $row): array => [
                        'tenant' => Arr::get($row, 'name', ''),
                        'cpu_percent' => Arr::get($row, 'cpu_percent', 0),
                        'memory_percent' => Arr::get($row, 'memory_percent', 0),
                        'db_load_percent' => Arr::get($row, 'db_load_percent', 0),
                    ])
                    ->values(),
            ],
        ];
    }

    protected function toPercent(float $value): int
    {
        return (int) max(0, min(100, round($value)));
    }

    /**
     * @return Collection<int, string>
     */
    protected function minuteLabels(Carbon $start, Carbon $end): Collection
    {
        $labels = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $labels->push($cursor->format('H:i'));
            $cursor->addMinute();
        }

        return $labels;
    }

    protected function memoryLimitMb(): int
    {
        $raw = trim((string) ini_get('memory_limit'));

        if ($raw === '' || $raw === '-1') {
            return 256;
        }

        $unit = strtolower(substr($raw, -1));
        $value = (float) $raw;

        if ($unit === 'g') {
            return (int) max(1, round($value * 1024));
        }

        if ($unit === 'k') {
            return (int) max(1, round($value / 1024));
        }

        if ($unit === 'm') {
            return (int) max(1, round($value));
        }

        return (int) max(1, round($value / 1024 / 1024));
    }

    protected function normalizeTenantId(string $tenantId): ?string
    {
        $trimmedTenantId = trim($tenantId);

        return $trimmedTenantId === '' ? null : $trimmedTenantId;
    }

    protected function normalizeRangeMinutes(string $rangeMinutes): int
    {
        $parsedRange = (int) trim($rangeMinutes);
        $allowedRanges = collect($this->rangeOptions())
            ->pluck('value')
            ->all();

        if (! in_array($parsedRange, $allowedRanges, true)) {
            return 30;
        }

        return $parsedRange;
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    protected function rangeOptions(): array
    {
        return [
            ['value' => 15, 'label' => 'Last 15 minutes'],
            ['value' => 30, 'label' => 'Last 30 minutes'],
            ['value' => 60, 'label' => 'Last 1 hour'],
            ['value' => 360, 'label' => 'Last 6 hours'],
            ['value' => 1440, 'label' => 'Last 24 hours'],
        ];
    }
}
