<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-300/80">Central App</p>
                <h2 class="text-2xl font-semibold text-slate-100">Tenant Monitoring</h2>
            </div>
            <p class="text-xs text-slate-400">Auto-refresh every 15 seconds</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="tenantMonitoringDashboard(@js($monitoringPayload), '{{ route('central.tenant-monitoring.data', [], false) }}')" x-init="init()">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-5 text-slate-200">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm text-cyan-200">Live tenant runtime monitoring from captured application telemetry.</p>
                    <p class="mt-1 text-xs text-slate-400">CPU and DB load are computed load estimates from tenant request throughput and timing. Memory uses peak request memory usage trends.</p>
                    <p class="mt-2 text-xs text-slate-500">As of: <span x-text="payload.as_of"></span></p>
                </div>

                <div class="grid w-full gap-3 lg:w-[32rem] lg:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-400" for="tenant-monitoring-filter">Tenant Scope</label>
                        <select
                            id="tenant-monitoring-filter"
                            x-model="selectedTenantId"
                            @change="onTenantFilterChange"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-sm text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-0"
                        >
                            <option value="">All tenants</option>
                            <template x-for="tenant in payload.tenants" :key="tenant.id">
                                <option :value="tenant.id" x-text="tenant.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-400" for="tenant-monitoring-range">Time Range</label>
                        <select
                            id="tenant-monitoring-range"
                            x-model.number="selectedRangeMinutes"
                            @change="onRangeFilterChange"
                            class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-sm text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-0"
                        >
                            <template x-for="option in payload.range_options" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Tenants Tracked</p>
                <p class="mt-2 text-2xl font-semibold text-slate-100" x-text="payload.summary.tenant_count"></p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Avg CPU</p>
                <p class="mt-2 text-2xl font-semibold text-amber-200"><span x-text="payload.summary.avg_cpu"></span>%</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Avg Memory</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-200"><span x-text="payload.summary.avg_memory"></span>%</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Avg DB Load</p>
                <p class="mt-2 text-2xl font-semibold text-rose-200"><span x-text="payload.summary.avg_db_load"></span>%</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Total RPM</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-200" x-text="Number(payload.summary.total_requests_per_minute).toFixed(1)"></p>
            </article>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <h3 class="text-lg font-semibold text-slate-100">Requests Per Minute (Last 30m)</h3>
                <p class="mt-1 text-xs text-slate-400" x-text="selectedTenantId ? 'Line chart for selected tenant traffic trend.' : 'Line chart for traffic comparison across your busiest tenants.'"></p>
                <div class="mt-4 h-72">
                    <canvas id="rpmLineChart"></canvas>
                </div>
            </article>

            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <h3 class="text-lg font-semibold text-slate-100">CPU Load Estimate (Last 30m)</h3>
                <p class="mt-1 text-xs text-slate-400" x-text="selectedTenantId ? 'Line chart shows selected tenant CPU pressure over time.' : 'Line chart highlights tenant spikes and sustained pressure.'"></p>
                <div class="mt-4 h-72">
                    <canvas id="cpuLineChart"></canvas>
                </div>
            </article>
        </div>

        <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
            <h3 class="text-lg font-semibold text-slate-100">CPU vs Memory vs DB Load</h3>
            <p class="mt-1 text-xs text-slate-400" x-text="selectedTenantId ? 'Grouped bar chart for selected tenant current resource profile.' : 'Grouped bar chart for side-by-side tenant resource comparison.'"></p>
            <div class="mt-4 h-80">
                <canvas id="resourceBarChart"></canvas>
            </div>
        </article>

        <div class="grid gap-5 xl:grid-cols-2">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <h3 class="text-lg font-semibold text-slate-100">Top Anomaly Tenants</h3>
                <p class="mt-1 text-xs text-slate-400">Anomaly score blends error rate, latency pressure, CPU, DB load, and memory.</p>
                <div class="mt-4 space-y-2">
                    <template x-for="row in payload.anomaly_rankings" :key="'anomaly-' + row.id">
                        <div class="flex items-center justify-between rounded-xl border px-3 py-2"
                             :class="isHighlighted(row.id)
                                ? 'border-amber-300/60 bg-amber-500/15'
                                : 'border-white/10 bg-slate-950/50'">
                            <div>
                                <p class="text-sm font-medium text-slate-100" x-text="row.name"></p>
                                <p class="text-xs text-slate-400">
                                    Error: <span x-text="Number(row.error_rate_percent || 0).toFixed(1)"></span>% ·
                                    Avg response: <span x-text="Number(row.avg_response_ms || 0).toFixed(1)"></span> ms
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-amber-200">
                                Score <span x-text="Number(row.anomaly_score || 0).toFixed(1)"></span>
                            </p>
                        </div>
                    </template>
                    <p x-show="payload.anomaly_rankings.length === 0" class="text-sm text-slate-400">No anomaly data yet.</p>
                </div>
            </article>

            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <h3 class="text-lg font-semibold text-slate-100">Endpoint Breakdown</h3>
                <p class="mt-1 text-xs text-slate-400">Detailed route pressure for the selected tenant and time window.</p>

                <div x-show="!selectedTenantId" class="mt-4 rounded-xl border border-dashed border-white/15 bg-slate-950/40 px-4 py-5 text-sm text-slate-400">
                    Select a tenant to inspect endpoint-level performance.
                </div>

                <div x-show="selectedTenantId" class="mt-4 overflow-x-auto rounded-xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                        <thead class="bg-slate-950/70 text-xs uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium">Path</th>
                                <th class="px-3 py-2 text-left font-medium">Route</th>
                                <th class="px-3 py-2 text-left font-medium">Requests</th>
                                <th class="px-3 py-2 text-left font-medium">Avg Response</th>
                                <th class="px-3 py-2 text-left font-medium">Avg DB</th>
                                <th class="px-3 py-2 text-left font-medium">Error Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <template x-for="row in payload.endpoint_breakdown" :key="'ep-' + row.request_path + '-' + row.route_name">
                                <tr>
                                    <td class="px-3 py-2 text-slate-100" x-text="row.request_path"></td>
                                    <td class="px-3 py-2 text-slate-400" x-text="row.route_name || '-' "></td>
                                    <td class="px-3 py-2 text-emerald-200" x-text="row.request_count"></td>
                                    <td class="px-3 py-2 text-slate-300" x-text="Number(row.avg_response_ms || 0).toFixed(1) + ' ms'"></td>
                                    <td class="px-3 py-2 text-slate-300" x-text="Number(row.avg_db_ms || 0).toFixed(1) + ' ms'"></td>
                                    <td class="px-3 py-2 text-rose-200" x-text="Number(row.error_rate_percent || 0).toFixed(1) + '%' "></td>
                                </tr>
                            </template>
                            <tr x-show="payload.endpoint_breakdown.length === 0">
                                <td colspan="6" class="px-3 py-4 text-center text-slate-400">No endpoint telemetry for this tenant in selected range.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                <thead class="bg-slate-950/60 text-xs uppercase tracking-[0.12em] text-slate-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Tenant</th>
                        <th class="px-4 py-3 text-left font-medium">Plan</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Req/Min</th>
                        <th class="px-4 py-3 text-left font-medium">Avg Response</th>
                        <th class="px-4 py-3 text-left font-medium">Avg DB</th>
                        <th class="px-4 py-3 text-left font-medium">CPU</th>
                        <th class="px-4 py-3 text-left font-medium">Memory</th>
                        <th class="px-4 py-3 text-left font-medium">DB Load</th>
                        <th class="px-4 py-3 text-left font-medium">Anomaly</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <template x-for="row in payload.tenant_rows" :key="row.id">
                        <tr :class="isHighlighted(row.id) ? 'bg-amber-500/10' : ''">
                            <td class="px-4 py-3 font-medium text-slate-100" x-text="row.name"></td>
                            <td class="px-4 py-3 text-slate-300" x-text="String(row.plan || '').toUpperCase()"></td>
                            <td class="px-4 py-3 text-slate-300" x-text="String(row.status || '').toUpperCase()"></td>
                            <td class="px-4 py-3 text-emerald-200" x-text="Number(row.requests_per_minute || 0).toFixed(1)"></td>
                            <td class="px-4 py-3 text-slate-300" x-text="Number(row.avg_response_ms || 0).toFixed(1) + ' ms'"></td>
                            <td class="px-4 py-3 text-slate-300" x-text="Number(row.avg_db_ms || 0).toFixed(1) + ' ms'"></td>
                            <td class="px-4 py-3 text-amber-200" x-text="row.cpu_percent + '%' "></td>
                            <td class="px-4 py-3 text-cyan-200" x-text="row.memory_percent + '%' "></td>
                            <td class="px-4 py-3 text-rose-200" x-text="row.db_load_percent + '%' "></td>
                            <td class="px-4 py-3 text-amber-100" x-text="Number(row.anomaly_score || 0).toFixed(1)"></td>
                        </tr>
                    </template>
                    <tr x-show="payload.tenant_rows.length === 0">
                        <td colspan="10" class="px-4 py-6 text-center text-slate-400">No tenant telemetry yet. Generate traffic in tenant apps to populate metrics.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        function tenantMonitoringDashboard(initialPayload, dataUrl) {
            return {
                payload: initialPayload,
                dataUrl,
                selectedTenantId: initialPayload.selected_tenant_id || '',
                selectedRangeMinutes: Number(initialPayload.selected_range_minutes || 30),
                charts: {
                    rpm: null,
                    cpu: null,
                    bar: null,
                },
                init() {
                    this.renderCharts();
                    window.setInterval(() => this.refresh(), 15000);
                },
                async refresh() {
                    try {
                        const endpoint = this.buildDataEndpoint();

                        const response = await fetch(endpoint, {
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                        });

                        if (!response.ok) {
                            return;
                        }

                        this.payload = await response.json();
                        this.selectedTenantId = this.payload.selected_tenant_id || '';
                        this.selectedRangeMinutes = Number(this.payload.selected_range_minutes || 30);
                        this.updateCharts();
                    } catch (error) {
                        // Silently ignore transient fetch errors in polling loop.
                    }
                },
                onTenantFilterChange() {
                    this.syncUrlState();
                    this.refresh();
                },
                onRangeFilterChange() {
                    this.syncUrlState();
                    this.refresh();
                },
                syncUrlState() {
                    const url = new URL(window.location.href);

                    if (this.selectedTenantId) {
                        url.searchParams.set('tenant_id', this.selectedTenantId);
                    } else {
                        url.searchParams.delete('tenant_id');
                    }

                    url.searchParams.set('range_minutes', String(this.selectedRangeMinutes));

                    window.history.replaceState({}, '', url.toString());
                },
                buildDataEndpoint() {
                    const url = new URL(this.dataUrl, window.location.origin);

                    if (this.selectedTenantId) {
                        url.searchParams.set('tenant_id', this.selectedTenantId);
                    }

                    url.searchParams.set('range_minutes', String(this.selectedRangeMinutes));

                    return url.toString();
                },
                isHighlighted(tenantId) {
                    return Array.isArray(this.payload.highlighted_tenant_ids)
                        && this.payload.highlighted_tenant_ids.includes(tenantId);
                },
                chartPalette(index) {
                    const colors = ['#22d3ee', '#f59e0b', '#34d399', '#f472b6', '#a78bfa', '#60a5fa'];
                    return colors[index % colors.length];
                },
                lineDatasets(series) {
                    return series.map((entry, index) => {
                        const color = this.chartPalette(index);

                        return {
                            label: entry.label,
                            data: entry.data,
                            borderColor: color,
                            backgroundColor: color,
                            borderWidth: 2,
                            pointRadius: 1,
                            tension: 0.35,
                        };
                    });
                },
                renderCharts() {
                    const labels = this.payload.charts.labels;

                    this.charts.rpm = new Chart(document.getElementById('rpmLineChart'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: this.lineDatasets(this.payload.charts.requests_per_minute_series),
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                y: {beginAtZero: true, grid: {color: 'rgba(148, 163, 184, 0.2)'}},
                                x: {grid: {display: false}},
                            },
                            plugins: {legend: {labels: {color: '#cbd5e1'}}},
                        },
                    });

                    this.charts.cpu = new Chart(document.getElementById('cpuLineChart'), {
                        type: 'line',
                        data: {
                            labels,
                            datasets: this.lineDatasets(this.payload.charts.cpu_series),
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                y: {beginAtZero: true, max: 100, grid: {color: 'rgba(148, 163, 184, 0.2)'}},
                                x: {grid: {display: false}},
                            },
                            plugins: {legend: {labels: {color: '#cbd5e1'}}},
                        },
                    });

                    this.charts.bar = new Chart(document.getElementById('resourceBarChart'), {
                        type: 'bar',
                        data: {
                            labels: this.payload.charts.bar_comparison.map((row) => row.tenant),
                            datasets: [
                                {
                                    label: 'CPU %',
                                    data: this.payload.charts.bar_comparison.map((row) => row.cpu_percent),
                                    backgroundColor: 'rgba(245, 158, 11, 0.7)',
                                },
                                {
                                    label: 'Memory %',
                                    data: this.payload.charts.bar_comparison.map((row) => row.memory_percent),
                                    backgroundColor: 'rgba(34, 211, 238, 0.7)',
                                },
                                {
                                    label: 'DB Load %',
                                    data: this.payload.charts.bar_comparison.map((row) => row.db_load_percent),
                                    backgroundColor: 'rgba(244, 114, 182, 0.7)',
                                },
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                y: {beginAtZero: true, max: 100, grid: {color: 'rgba(148, 163, 184, 0.2)'}},
                                x: {grid: {display: false}},
                            },
                            plugins: {legend: {labels: {color: '#cbd5e1'}}},
                        },
                    });
                },
                updateCharts() {
                    if (!this.charts.rpm || !this.charts.cpu || !this.charts.bar) {
                        return;
                    }

                    this.charts.rpm.data.labels = this.payload.charts.labels;
                    this.charts.rpm.data.datasets = this.lineDatasets(this.payload.charts.requests_per_minute_series);
                    this.charts.rpm.update();

                    this.charts.cpu.data.labels = this.payload.charts.labels;
                    this.charts.cpu.data.datasets = this.lineDatasets(this.payload.charts.cpu_series);
                    this.charts.cpu.update();

                    this.charts.bar.data.labels = this.payload.charts.bar_comparison.map((row) => row.tenant);
                    this.charts.bar.data.datasets[0].data = this.payload.charts.bar_comparison.map((row) => row.cpu_percent);
                    this.charts.bar.data.datasets[1].data = this.payload.charts.bar_comparison.map((row) => row.memory_percent);
                    this.charts.bar.data.datasets[2].data = this.payload.charts.bar_comparison.map((row) => row.db_load_percent);
                    this.charts.bar.update();
                },
            };
        }
    </script>
</x-app-layout>
