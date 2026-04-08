<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-300/80">Central App</p>
            <h2 class="text-2xl font-semibold text-slate-100">Business Control</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-7">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Active Plans</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['activePlans']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Active Coupons</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['activeCoupons']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Live Campaigns</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['activeCampaigns']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Pending Upgrades</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['pendingUpgradeRequests']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Active Schools</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['activeUniversities']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Schools on Basic</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['basicSchools']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Schools on Pro</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format((int) $metrics['proSchools']) }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            <a href="{{ route('central.business-control.plans.index') }}" class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5 transition hover:border-cyan-300 hover:bg-cyan-100 dark:border-cyan-300/20 dark:bg-cyan-500/10 dark:hover:border-cyan-300/40 dark:hover:bg-cyan-500/20">
                <p class="text-sm font-semibold text-cyan-900 dark:text-cyan-100">Plan Management</p>
                <p class="mt-1 text-xs text-cyan-700 dark:text-cyan-200/80">Create, activate, and tune monthly and yearly pricing.</p>
            </a>
            <a href="{{ route('central.business-control.coupons.index') }}" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-300/20 dark:bg-emerald-500/10 dark:hover:border-emerald-300/40 dark:hover:bg-emerald-500/20">
                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Coupon Management</p>
                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-200/80">Control promotions, limits, and plan targeting.</p>
            </a>
            <a href="{{ route('central.business-control.campaigns.index') }}" class="rounded-2xl border border-fuchsia-200 bg-fuchsia-50 p-5 transition hover:border-fuchsia-300 hover:bg-fuchsia-100 dark:border-fuchsia-300/20 dark:bg-fuchsia-500/10 dark:hover:border-fuchsia-300/40 dark:hover:bg-fuchsia-500/20">
                <p class="text-sm font-semibold text-fuchsia-900 dark:text-fuchsia-100">Campaign Management</p>
                <p class="mt-1 text-xs text-fuchsia-700 dark:text-fuchsia-200/80">Run Black Friday style promotions with orchestration rules.</p>
            </a>
            <a href="{{ route('central.business-control.upgrade-requests.index') }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 transition hover:border-amber-300 hover:bg-amber-100 dark:border-amber-300/20 dark:bg-amber-500/10 dark:hover:border-amber-300/40 dark:hover:bg-amber-500/20">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Upgrade Queue</p>
                <p class="mt-1 text-xs text-amber-700 dark:text-amber-200/80">Review and process tenant upgrade requests.</p>
            </a>
            <a href="{{ route('central.business-control.support-updates.index') }}" class="rounded-2xl border border-blue-200 bg-blue-50 p-5 transition hover:border-blue-300 hover:bg-blue-100 dark:border-blue-300/20 dark:bg-blue-500/10 dark:hover:border-blue-300/40 dark:hover:bg-blue-500/20">
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">Support &amp; Updates</p>
                <p class="mt-1 text-xs text-blue-700 dark:text-blue-200/80">See tenant support reports and publish product updates.</p>
            </a>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-white/10 dark:bg-slate-900/80">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recent Pending Requests</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($recentPendingRequests as $request)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-950/60">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $request->university?->name ?? $request->tenant_id }}</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                                {{ strtoupper($request->requested_plan) }} · {{ strtoupper($request->billing_cycle) }} · Final ${{ number_format((float) $request->final_price, 2) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400">No pending requests right now.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Active Plans Snapshot</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                        @forelse ($activePlans as $plan)
                            <div class="flex items-center justify-between rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-950/50">
                                <span>{{ strtoupper($plan->code) }}</span>
                                <span>${{ number_format((float) $plan->monthly_price, 2) }}/mo</span>
                            </div>
                        @empty
                            <p class="text-slate-500 dark:text-slate-400">No active plans.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Billing Cycle Distribution</h3>
                    @php
                        $billingTotal = (int) (($billingDistribution['monthly'] ?? 0) + ($billingDistribution['yearly'] ?? 0));
                        $monthlyPercent = $billingTotal > 0 ? (($billingDistribution['monthly'] ?? 0) / $billingTotal) * 100 : 0;
                        $yearlyPercent = $billingTotal > 0 ? (($billingDistribution['yearly'] ?? 0) / $billingTotal) * 100 : 0;
                    @endphp
                    <div class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                        <div class="flex items-center justify-between rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-950/50">
                            <span>Monthly</span>
                            <span>{{ (int) ($billingDistribution['monthly'] ?? 0) }} ({{ number_format($monthlyPercent, 1) }}%)</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-950/50">
                            <span>Yearly</span>
                            <span>{{ (int) ($billingDistribution['yearly'] ?? 0) }} ({{ number_format($yearlyPercent, 1) }}%)</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">Active Coupons Snapshot</h3>
                    <div class="mt-3 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                        @forelse ($activeCoupons as $coupon)
                            <div class="flex items-center justify-between rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-950/50">
                                <span>{{ strtoupper($coupon->code) }}</span>
                                <span>{{ strtoupper($coupon->discount_type) }} {{ number_format((float) $coupon->discount_value, 2) }}</span>
                            </div>
                        @empty
                            <p class="text-slate-500 dark:text-slate-400">No active coupons.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
