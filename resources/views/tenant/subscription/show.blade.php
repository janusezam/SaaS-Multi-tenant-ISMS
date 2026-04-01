<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Subscription</h2>
            @if ($pendingUpgradeRequest)
                <span class="rounded-full border border-amber-300/40 bg-amber-500/20 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-100">Upgrade Pending</span>
            @elseif (tenant()?->currentPlan() !== 'pro')
                <button type="button" data-upgrade-trigger class="rounded-xl border border-amber-300/40 bg-amber-500/20 px-4 py-2 text-sm font-medium text-amber-100 hover:bg-amber-500/30">
                    Upgrade to Pro
                </button>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Current Plan</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-200">{{ strtoupper((string) ($subscription?->plan ?? tenant()?->currentPlan() ?? 'basic')) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Billing Cycle</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-200">{{ strtoupper((string) ($subscription?->billing_cycle ?? 'monthly')) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Effective Price</p>
                <p class="mt-2 text-2xl font-semibold text-cyan-200">${{ number_format((float) ($subscription?->final_price ?? 0), 2) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <h3 class="text-lg font-semibold text-slate-100">Subscription Details</h3>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Start Date</p>
                    <p class="mt-1 text-sm text-slate-100">{{ $subscription?->start_date?->format('M d, Y') ?? 'Not set' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Expiry Date</p>
                    <p class="mt-1 text-sm text-slate-100">{{ $subscription?->due_date?->format('M d, Y') ?? 'Not set' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Applied Coupon</p>
                    <p class="mt-1 text-sm text-slate-100">{{ $subscription?->coupon_code ?: 'None' }}</p>
                </div>
            </div>

            @if ($pendingUpgradeRequest)
                <div class="mt-5 rounded-xl border border-amber-300/30 bg-amber-500/10 p-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-amber-100">Pending Upgrade Request</p>
                    <p class="mt-1 text-sm text-slate-100">
                        {{ strtoupper($pendingUpgradeRequest->requested_plan) }} · {{ strtoupper($pendingUpgradeRequest->billing_cycle) }} ·
                        ${{ number_format((float) $pendingUpgradeRequest->final_price, 2) }}
                    </p>
                </div>
            @endif

            @if ($proPlan)
                <div class="mt-5 text-xs text-slate-400">
                    Pro pricing: ${{ number_format((float) $proPlan->monthly_price, 2) }}/month or ${{ number_format((float) $proPlan->yearly_price, 2) }}/year
                    @if ((float) $proPlan->yearly_discount_percent > 0)
                        (save {{ number_format((float) $proPlan->yearly_discount_percent, 2) }}%).
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($openUpgradeModal)
        <script>
            window.__ismsOpenUpgradeModalOnLoad = true;
        </script>
    @endif
</x-app-layout>
