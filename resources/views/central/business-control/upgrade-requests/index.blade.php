<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Upgrade Requests</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Pending Requests</h3>

            <div class="mt-4 space-y-4">
                @forelse ($pendingRequests as $upgradeRequest)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-950/60">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $upgradeRequest->university?->name ?? $upgradeRequest->tenant_id }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    Current: {{ strtoupper((string) ($upgradeRequest->university?->subscription?->plan ?? $upgradeRequest->university?->plan ?? 'basic')) }} ·
                                    Requested: {{ strtoupper($upgradeRequest->requested_plan) }} ·
                                    {{ strtoupper($upgradeRequest->billing_cycle) }} ·
                                    Base: ${{ number_format((float) $upgradeRequest->base_price, 2) }} ·
                                    Discount: ${{ number_format((float) $upgradeRequest->discount_amount, 2) }} ·
                                    Effective: ${{ number_format((float) $upgradeRequest->final_price, 2) }}
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-400">Coupon: {{ $upgradeRequest->coupon_code ?: 'None' }} · Requested by {{ $upgradeRequest->requested_by_email }}</p>
                                <p class="mt-2">
                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:border-amber-300/30 dark:bg-amber-500/20 dark:text-amber-100">Pending Review</span>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('central.business-control.upgrade-requests.approve', $upgradeRequest) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" step="0.01" min="0" name="manual_price_override" placeholder="Manual final price (optional)" class="rounded border border-slate-300 bg-white text-sm text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/70 dark:text-slate-100">
                                    <button type="submit" class="rounded-md border border-emerald-300 bg-emerald-100 px-3 py-1 text-xs text-emerald-800 hover:bg-emerald-200 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('central.business-control.upgrade-requests.reject', $upgradeRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md border border-rose-300 bg-rose-100 px-3 py-1 text-xs text-rose-800 hover:bg-rose-200 dark:border-rose-300/30 dark:bg-rose-500/20 dark:text-rose-100 dark:hover:bg-rose-500/30">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No pending requests.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recently Processed</h3>
            <div class="mt-4 space-y-3">
                @forelse ($recentlyProcessedRequests as $upgradeRequest)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-200">
                        <p>
                            <span class="font-medium">{{ $upgradeRequest->university?->name ?? $upgradeRequest->tenant_id }}</span>
                            · {{ strtoupper($upgradeRequest->requested_plan) }}
                            ·
                            <span @class([
                                'inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold align-middle',
                                'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100' => $upgradeRequest->status === 'approved',
                                'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-300/30 dark:bg-rose-500/20 dark:text-rose-100' => $upgradeRequest->status === 'rejected',
                                'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300' => ! in_array($upgradeRequest->status, ['approved', 'rejected'], true),
                            ])>{{ strtoupper($upgradeRequest->status) }}</span>
                            · Final ${{ number_format((float) $upgradeRequest->final_price, 2) }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No processed requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
