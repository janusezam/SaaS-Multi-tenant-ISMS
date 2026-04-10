<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Subscription</h2>
            @if ($pendingUpgradeRequest)
                <span class="rounded-full border border-amber-300 bg-amber-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-800 dark:border-amber-300/40 dark:bg-amber-500/20 dark:text-amber-100">Upgrade Pending</span>
            @elseif ($recommendedPlan)
                <button
                    type="button"
                    data-upgrade-trigger
                    data-page-upgrade-trigger
                    data-plan-code="{{ $recommendedPlan->code }}"
                    data-plan-name="{{ $recommendedPlan->name }}"
                    class="rounded-xl border border-amber-300 bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-200 dark:border-amber-300/40 dark:bg-amber-500/20 dark:text-amber-100 dark:hover:bg-amber-500/30"
                >
                    Change plan
                </button>
            @endif
        </div>
    </x-slot>

    @php
        $basicMonthlyQuote = data_get($pricingByPlan ?? [], 'basic.monthly');
        $basicYearlyQuote = data_get($pricingByPlan ?? [], 'basic.yearly');
        $proMonthlyQuote = data_get($pricingByPlan ?? [], 'pro.monthly');
        $proYearlyQuote = data_get($pricingByPlan ?? [], 'pro.yearly');

        $basicMonthlyPrice = (float) data_get($basicMonthlyQuote, 'final_price', $basicPlan?->monthly_price ?? 0);
        $basicYearlyPrice = (float) data_get($basicYearlyQuote, 'final_price', $basicPlan?->yearly_price ?? 0);
        $proMonthlyPrice = (float) data_get($proMonthlyQuote, 'final_price', $proPlan?->monthly_price ?? 0);
        $proYearlyPrice = (float) data_get($proYearlyQuote, 'final_price', $proPlan?->yearly_price ?? 0);

        $basicMonthlyBasePrice = (float) data_get($basicMonthlyQuote, 'base_price', $basicPlan?->monthly_price ?? 0);
        $basicYearlyBasePrice = (float) data_get($basicYearlyQuote, 'base_price', $basicPlan?->yearly_price ?? 0);
        $proMonthlyBasePrice = (float) data_get($proMonthlyQuote, 'base_price', $proPlan?->monthly_price ?? 0);
        $proYearlyBasePrice = (float) data_get($proYearlyQuote, 'base_price', $proPlan?->yearly_price ?? 0);

        $basicCampaignName = (string) data_get($basicMonthlyQuote, 'campaign.name', data_get($basicYearlyQuote, 'campaign.name', ''));
        $proCampaignName = (string) data_get($proMonthlyQuote, 'campaign.name', data_get($proYearlyQuote, 'campaign.name', ''));

        $basicHasCampaignPrice = $basicCampaignName !== '' && ($basicMonthlyPrice < $basicMonthlyBasePrice || $basicYearlyPrice < $basicYearlyBasePrice);
        $proHasCampaignPrice = $proCampaignName !== '' && ($proMonthlyPrice < $proMonthlyBasePrice || $proYearlyPrice < $proYearlyBasePrice);

        $basicYearlyMonthlyEquivalent = $basicYearlyPrice > 0 ? ($basicYearlyPrice / 12) : 0;
        $proYearlyMonthlyEquivalent = $proYearlyPrice > 0 ? ($proYearlyPrice / 12) : 0;

        $basicYearlySavingsAmount = max(0, ($basicMonthlyPrice * 12) - $basicYearlyPrice);
        $proYearlySavingsAmount = max(0, ($proMonthlyPrice * 12) - $proYearlyPrice);

        $basicYearlySavingsPercent = ($basicMonthlyPrice * 12) > 0
            ? ($basicYearlySavingsAmount / ($basicMonthlyPrice * 12)) * 100
            : (float) ($basicPlan?->yearly_discount_percent ?? 0);

        $proYearlySavingsPercent = ($proMonthlyPrice * 12) > 0
            ? ($proYearlySavingsAmount / ($proMonthlyPrice * 12)) * 100
            : (float) ($proPlan?->yearly_discount_percent ?? 0);

        $defaultBillingCycle = $currentBillingCycle === 'yearly' ? 'yearly' : 'monthly';
        $isCurrentPlanBasic = $currentPlanCode === 'basic';
        $isCurrentPlanPro = $currentPlanCode === 'pro';
        $limitText = static fn (?int $value): string => $value === null ? 'Unlimited' : number_format($value);

        $basicLimits = [
            'users' => $basicPlan?->max_users,
            'teams' => $basicPlan?->max_teams,
            'sports' => $basicPlan?->max_sports,
        ];

        $proLimits = [
            'users' => $proPlan?->max_users,
            'teams' => $proPlan?->max_teams,
            'sports' => $proPlan?->max_sports,
        ];

        $usageKeys = ['users', 'teams', 'sports'];

        $activeCampaignNames = collect([
            $basicCampaignName,
            $proCampaignName,
            ...$additionalPlans->map(function ($plan) use ($pricingByPlan): string {
                return (string) data_get($pricingByPlan ?? [], $plan->code.'.monthly.campaign.name', data_get($pricingByPlan ?? [], $plan->code.'.yearly.campaign.name', ''));
            })->all(),
        ])->filter(fn (string $name): bool => trim($name) !== '')->unique()->values();
    @endphp

    <div
        class="tenant-subscription-pricing mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8"
        data-subscription-pricing-root
        data-default-cycle="{{ $defaultBillingCycle }}"
        data-page-pending="{{ $pendingUpgradeRequest ? '1' : '0' }}"
        data-can-submit-upgrade="{{ $canSubmitUpgradeRequest ? '1' : '0' }}"
        data-basic-monthly="{{ number_format($basicMonthlyPrice, 2, '.', '') }}"
        data-basic-yearly="{{ number_format($basicYearlyPrice, 2, '.', '') }}"
        data-basic-yearly-equivalent="{{ number_format($basicYearlyMonthlyEquivalent, 2, '.', '') }}"
        data-basic-yearly-savings-amount="{{ number_format($basicYearlySavingsAmount, 2, '.', '') }}"
        data-basic-yearly-savings-percent="{{ number_format($basicYearlySavingsPercent, 2, '.', '') }}"
        data-pro-monthly="{{ number_format($proMonthlyPrice, 2, '.', '') }}"
        data-pro-yearly="{{ number_format($proYearlyPrice, 2, '.', '') }}"
        data-pro-yearly-equivalent="{{ number_format($proYearlyMonthlyEquivalent, 2, '.', '') }}"
        data-pro-yearly-savings-amount="{{ number_format($proYearlySavingsAmount, 2, '.', '') }}"
        data-pro-yearly-savings-percent="{{ number_format($proYearlySavingsPercent, 2, '.', '') }}"
    >
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($activeCampaignNames->isNotEmpty())
            <div class="rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                Campaign sale active: {{ $activeCampaignNames->join(', ') }}. Displayed plan prices already include campaign discounts.
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/85">
            <div class="grid gap-0 divide-y divide-slate-200 text-sm dark:divide-white/10 md:grid-cols-5 md:divide-x md:divide-y-0">
                <div class="px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">School</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $tenantName }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Current Plan</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ strtoupper($currentPlanCode) }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Billing Cycle</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ strtoupper($currentBillingCycle) }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Expiry Date</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">{{ $expiryDate?->format('M d, Y') ?? 'Not set' }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Effective Price</p>
                    <p class="mt-1 font-semibold text-slate-900 dark:text-slate-100">${{ number_format($effectivePrice, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/85">
            <div class="px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-slate-600 dark:text-slate-300">Plan Limits and Usage</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach ($usageKeys as $key)
                        @php
                            $used = $resourceUsage[$key] ?? null;
                            $limit = $resourceLimits[$key] ?? null;
                            $remaining = $used !== null && $limit !== null ? max(0, $limit - $used) : null;
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-slate-950/40">
                            <p class="text-xs uppercase tracking-[0.12em] text-slate-500 dark:text-slate-400">{{ ucfirst($key) }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                {{ $used !== null ? number_format($used) : 'N/A' }} / {{ $limitText($limit) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                @if ($remaining !== null)
                                    Remaining: {{ number_format($remaining) }}
                                @else
                                    No hard cap
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
            <div class="inline-flex rounded-full border border-slate-300 bg-slate-100 p-1 dark:border-white/15 dark:bg-slate-900/70">
                <button
                    type="button"
                    data-billing-toggle="monthly"
                    class="rounded-full px-4 py-1.5 text-sm font-medium text-slate-700 transition dark:text-slate-300"
                >
                    Monthly
                </button>
                <button
                    type="button"
                    data-billing-toggle="yearly"
                    class="rounded-full px-4 py-1.5 text-sm font-medium text-slate-700 transition dark:text-slate-300"
                >
                    Yearly
                </button>
            </div>

            <span
                data-yearly-toggle-savings
                class="hidden rounded-full border border-emerald-300 bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100"
            ></span>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/85">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Basic</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">Basic Plan</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Core intramurals operations for school-wide scheduling and management.</p>

                <div class="mt-5">
                    <p class="text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="basic" data-price-cycle="monthly">
                        ${{ number_format($basicMonthlyPrice, 2) }}
                    </p>
                    @if ($basicHasCampaignPrice)
                        <p class="mt-1 text-xs text-slate-500 line-through dark:text-slate-400">Regular: ${{ number_format($basicMonthlyBasePrice, 2) }}/month</p>
                    @endif
                    <p class="hidden text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="basic" data-price-cycle="yearly">
                        ${{ number_format($basicYearlyMonthlyEquivalent, 2) }}
                        <span class="text-base font-medium text-slate-500 dark:text-slate-400">/month</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-yearly-note="basic">Billed annually at ${{ number_format($basicYearlyPrice, 2) }}@if ($basicHasCampaignPrice) <span class="line-through">${{ number_format($basicYearlyBasePrice, 2) }}</span>@endif</p>
                    <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-200" data-yearly-save="basic" data-plan-yearly-savings-percent="{{ number_format($basicYearlySavingsPercent, 2, '.', '') }}">
                        Save ${{ number_format($basicYearlySavingsAmount, 2) }} ({{ number_format($basicYearlySavingsPercent, 2) }}%) yearly
                    </p>
                    @if ($basicHasCampaignPrice)
                        <p class="mt-1 text-xs font-medium text-emerald-300">Campaign active: {{ $basicCampaignName }}</p>
                    @endif
                </div>

                <button
                    type="button"
                    class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 dark:border-white/20 dark:bg-slate-800 dark:text-slate-300"
                    data-basic-current-plan-btn
                    disabled
                >
                    {{ $isCurrentPlanBasic ? 'Your current plan' : 'Basic plan' }}
                </button>

                <hr class="my-6 border-slate-200 dark:border-white/10">

                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Sports, venues, teams, players, and schedules management</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Up to {{ $limitText($basicLimits['users']) }} users, {{ $limitText($basicLimits['teams']) }} teams, and {{ $limitText($basicLimits['sports']) }} sports</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Result submission and standings computation</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Game result audit trail</span></li>
                    <li class="flex items-start gap-2 text-slate-500 dark:text-slate-400"><span class="mt-[2px] text-rose-500 dark:text-rose-300">✕</span><span>Advanced analytics <span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] uppercase tracking-[0.12em] text-rose-700 dark:bg-rose-500/20 dark:text-rose-200">Pro only</span></span></li>
                    <li class="flex items-start gap-2 text-slate-500 dark:text-slate-400"><span class="mt-[2px] text-rose-500 dark:text-rose-300">✕</span><span>Bracket management and match progression <span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] uppercase tracking-[0.12em] text-rose-700 dark:bg-rose-500/20 dark:text-rose-200">Pro only</span></span></li>
                    <li class="flex items-start gap-2 text-slate-500 dark:text-slate-400"><span class="mt-[2px] text-rose-500 dark:text-rose-300">✕</span><span>CSV and PDF exports <span class="ml-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] uppercase tracking-[0.12em] text-rose-700 dark:bg-rose-500/20 dark:text-rose-200">Pro only</span></span></li>
                </ul>
            </article>

            <article class="relative rounded-3xl border-2 border-cyan-300 bg-white p-6 shadow-sm dark:border-cyan-300/60 dark:bg-slate-900/85">
                <span class="absolute -top-3 right-5 rounded-full border border-cyan-300 bg-cyan-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-800 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100">Recommended</span>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-700 dark:text-cyan-300">Pro</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">Pro Plan</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Everything in Basic, plus premium analytics, brackets, and exports.</p>

                <div class="mt-5">
                    <p class="text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="pro" data-price-cycle="monthly">
                        ${{ number_format($proMonthlyPrice, 2) }}
                        <span class="text-base font-medium text-slate-500 dark:text-slate-400">/month</span>
                    </p>
                    @if ($proHasCampaignPrice)
                        <p class="mt-1 text-xs text-slate-500 line-through dark:text-slate-400">Regular: ${{ number_format($proMonthlyBasePrice, 2) }}/month</p>
                    @endif
                    <p class="hidden text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="pro" data-price-cycle="yearly">
                        ${{ number_format($proYearlyMonthlyEquivalent, 2) }}
                        <span class="text-base font-medium text-slate-500 dark:text-slate-400">/month</span>
                    </p>
                    <p class="mt-1 hidden text-sm text-slate-500 line-through dark:text-slate-400" data-yearly-original-monthly="pro">
                        ${{ number_format($proMonthlyPrice, 2) }}/month
                    </p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-yearly-note="pro">Billed annually at ${{ number_format($proYearlyPrice, 2) }}@if ($proHasCampaignPrice) <span class="line-through">${{ number_format($proYearlyBasePrice, 2) }}</span>@endif</p>
                    <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-200" data-yearly-save="pro" data-plan-yearly-savings-percent="{{ number_format($proYearlySavingsPercent, 2, '.', '') }}">
                        Save ${{ number_format($proYearlySavingsAmount, 2) }} ({{ number_format($proYearlySavingsPercent, 2) }}%) annually
                    </p>
                    @if ($proHasCampaignPrice)
                        <p class="mt-1 text-xs font-medium text-emerald-300">Campaign active: {{ $proCampaignName }}</p>
                    @endif
                </div>

                @if ($isCurrentPlanPro)
                    <button
                        type="button"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 dark:border-white/20 dark:bg-slate-800 dark:text-slate-300"
                        disabled
                    >
                        Your current plan
                    </button>
                @else
                    <button
                        type="button"
                        data-upgrade-trigger
                        data-page-upgrade-trigger
                        data-pro-upgrade-btn
                        data-plan-code="pro"
                        data-plan-name="Pro"
                        class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2.5 text-sm font-medium text-cyan-800 hover:bg-cyan-200 disabled:cursor-not-allowed disabled:opacity-60 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30"
                        @disabled($pendingUpgradeRequest || ! $canSubmitUpgradeRequest)
                    >
                        @if ($pendingUpgradeRequest)
                            Upgrade request pending
                        @elseif (! $canSubmitUpgradeRequest)
                            University admin only
                        @else
                            Request upgrade to Pro
                        @endif
                    </button>
                @endif

                <hr class="my-6 border-slate-200 dark:border-white/10">

                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Everything in Basic</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Up to {{ $limitText($proLimits['users']) }} users, {{ $limitText($proLimits['teams']) }} teams, and {{ $limitText($proLimits['sports']) }} sports</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Advanced intramural analytics dashboard</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Bracket generator with winner progression</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Bracket and result audit history</span></li>
                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>CSV and PDF exports for standings and audits</span></li>
                </ul>
            </article>
        </div>

        @if ($additionalPlans->isNotEmpty())
            <div class="grid gap-5 lg:grid-cols-2">
                @foreach ($additionalPlans as $plan)
                    @php
                        $planMonthlyQuote = data_get($pricingByPlan ?? [], $plan->code.'.monthly');
                        $planYearlyQuote = data_get($pricingByPlan ?? [], $plan->code.'.yearly');
                        $planMonthlyPrice = (float) data_get($planMonthlyQuote, 'final_price', $plan->monthly_price);
                        $planYearlyPrice = (float) data_get($planYearlyQuote, 'final_price', $plan->yearly_price);
                        $planMonthlyBasePrice = (float) data_get($planMonthlyQuote, 'base_price', $plan->monthly_price);
                        $planYearlyBasePrice = (float) data_get($planYearlyQuote, 'base_price', $plan->yearly_price);
                        $planYearlyMonthlyEquivalent = $planYearlyPrice > 0 ? ($planYearlyPrice / 12) : 0;
                        $planYearlySavingsAmount = max(0, ($planMonthlyPrice * 12) - $planYearlyPrice);
                        $planYearlySavingsPercent = ($planMonthlyPrice * 12) > 0
                            ? ($planYearlySavingsAmount / ($planMonthlyPrice * 12)) * 100
                            : (float) $plan->yearly_discount_percent;
                        $planCampaignName = (string) data_get($planMonthlyQuote, 'campaign.name', data_get($planYearlyQuote, 'campaign.name', ''));
                        $planHasCampaignPrice = $planCampaignName !== '' && ($planMonthlyPrice < $planMonthlyBasePrice || $planYearlyPrice < $planYearlyBasePrice);
                        $marketingPoints = collect(preg_split('/\r\n|\r|\n/', (string) ($plan->marketing_points ?? '')))
                            ->map(fn ($point) => trim((string) $point))
                            ->filter()
                            ->values();
                        $isCurrentPlan = $currentPlanCode === (string) $plan->code;
                    @endphp

                    <article class="relative rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/85">
                        @if ($plan->badge_label)
                            <span class="absolute -top-3 right-5 rounded-full border border-cyan-300 bg-cyan-100 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-800 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100">{{ $plan->badge_label }}</span>
                        @endif

                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ strtoupper($plan->code) }}</p>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $plan->name }}</h3>
                        @if ($plan->marketing_tagline)
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $plan->marketing_tagline }}</p>
                        @endif

                        <div class="mt-5">
                            <p class="text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="{{ $plan->code }}" data-price-cycle="monthly">
                                ${{ number_format($planMonthlyPrice, 2) }}
                                <span class="text-base font-medium text-slate-500 dark:text-slate-400">/month</span>
                            </p>
                            @if ($planHasCampaignPrice)
                                <p class="mt-1 text-xs text-slate-500 line-through dark:text-slate-400">Regular: ${{ number_format($planMonthlyBasePrice, 2) }}/month</p>
                            @endif
                            <p class="hidden text-4xl font-semibold text-slate-900 dark:text-slate-100" data-price-plan="{{ $plan->code }}" data-price-cycle="yearly">
                                ${{ number_format($planYearlyMonthlyEquivalent, 2) }}
                                <span class="text-base font-medium text-slate-500 dark:text-slate-400">/month</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-yearly-note="{{ $plan->code }}">Billed annually at ${{ number_format($planYearlyPrice, 2) }}@if ($planHasCampaignPrice) <span class="line-through">${{ number_format($planYearlyBasePrice, 2) }}</span>@endif</p>
                            <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-200" data-yearly-save="{{ $plan->code }}" data-plan-yearly-savings-percent="{{ number_format($planYearlySavingsPercent, 2, '.', '') }}">
                                Save ${{ number_format($planYearlySavingsAmount, 2) }} ({{ number_format($planYearlySavingsPercent, 2) }}%) annually
                            </p>
                            @if ($planHasCampaignPrice)
                                <p class="mt-1 text-xs font-medium text-emerald-300">Campaign active: {{ $planCampaignName }}</p>
                            @endif
                        </div>

                        @if ($isCurrentPlan)
                            <button
                                type="button"
                                class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 dark:border-white/20 dark:bg-slate-800 dark:text-slate-300"
                                disabled
                            >
                                Your current plan
                            </button>
                        @else
                            <button
                                type="button"
                                data-upgrade-trigger
                                data-page-upgrade-trigger
                                data-plan-code="{{ $plan->code }}"
                                data-plan-name="{{ $plan->name }}"
                                class="mt-5 inline-flex w-full items-center justify-center rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2.5 text-sm font-medium text-cyan-800 hover:bg-cyan-200 disabled:cursor-not-allowed disabled:opacity-60 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30"
                                @disabled($pendingUpgradeRequest || ! $canSubmitUpgradeRequest)
                            >
                                @if ($pendingUpgradeRequest)
                                    Upgrade request pending
                                @elseif (! $canSubmitUpgradeRequest)
                                    University admin only
                                @else
                                    {{ $plan->cta_label ?: 'Request plan change' }}
                                @endif
                            </button>
                        @endif

                        @if ($marketingPoints->isNotEmpty())
                            <hr class="my-6 border-slate-200 dark:border-white/10">
                            <ul class="space-y-2 text-sm">
                                @foreach ($marketingPoints as $point)
                                    <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>{{ $point }}</span></li>
                                @endforeach
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Users: {{ $limitText($plan->max_users) }}</span></li>
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Teams: {{ $limitText($plan->max_teams) }}</span></li>
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Sports: {{ $limitText($plan->max_sports) }}</span></li>
                            </ul>
                        @else
                            <hr class="my-6 border-slate-200 dark:border-white/10">
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Users: {{ $limitText($plan->max_users) }}</span></li>
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Teams: {{ $limitText($plan->max_teams) }}</span></li>
                                <li class="flex items-start gap-2 text-slate-700 dark:text-slate-200"><span class="mt-[2px] text-emerald-600 dark:text-emerald-300">✓</span><span>Sports: {{ $limitText($plan->max_sports) }}</span></li>
                            </ul>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        <div
            data-subscription-pending-notice
            class="{{ $pendingUpgradeRequest ? '' : 'hidden' }} rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-300/30 dark:bg-amber-500/10 dark:text-amber-100"
        >
            Your upgrade request is under administrator review. We will apply plan changes once approved by the system administrator.
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-300">
            No payment is required on this page. Upgrade requests are reviewed and activated by the system administrator.
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/85">
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Current Subscription Record</h3>
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Start Date</p>
                    <p class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription?->start_date?->format('M d, Y') ?? 'Not set' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Expiry Date</p>
                    <p class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $expiryDate?->format('M d, Y') ?? 'Not set' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Applied Coupon</p>
                    <p class="mt-1 text-sm text-slate-800 dark:text-slate-100">{{ $subscription?->coupon_code ?: 'None' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Last Effective Price</p>
                    <p class="mt-1 text-sm text-slate-800 dark:text-slate-100">${{ number_format($effectivePrice, 2) }}</p>
                </div>
            </div>
            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-white/10 dark:bg-slate-950/40 dark:text-slate-300">
                @if ($subscription?->nextRenewalCampaign)
                    Next renewal campaign: <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $subscription->nextRenewalCampaign->name }}</span>
                    (managed by central business control).
                @else
                    No renewal campaign assigned yet. Your next renewal uses current plan pricing policy.
                @endif
            </div>
        </div>
    </div>

    @if ($openUpgradeModal)
        <script>
            window.__ismsOpenUpgradeModalOnLoad = true;
        </script>
    @endif

    <script>
        (function () {
            var root = document.querySelector('[data-subscription-pricing-root]');

            if (!root) {
                return;
            }

            var monthlyButton = root.querySelector('[data-billing-toggle="monthly"]');
            var yearlyButton = root.querySelector('[data-billing-toggle="yearly"]');
            var savingsBadge = root.querySelector('[data-yearly-toggle-savings]');
            var pagePendingNotice = root.querySelector('[data-subscription-pending-notice]');
            var pageUpgradeButtons = root.querySelectorAll('[data-page-upgrade-trigger]');

            var state = {
                billingCycle: root.getAttribute('data-default-cycle') === 'yearly' ? 'yearly' : 'monthly',
                pending: root.getAttribute('data-page-pending') === '1',
            };

            function numberFromData(key) {
                return Number(root.getAttribute(key) || 0);
            }

            function maxSavingsPercent() {
                var percentages = Array.from(root.querySelectorAll('[data-plan-yearly-savings-percent]')).map(function (node) {
                    return Number(node.getAttribute('data-plan-yearly-savings-percent') || 0);
                });

                if (percentages.length === 0) {
                    return 0;
                }

                return Math.max.apply(Math, percentages);
            }

            function setButtonStates() {
                var monthlyActive = state.billingCycle === 'monthly';

                if (monthlyButton) {
                    monthlyButton.className = monthlyActive
                        ? 'rounded-full border border-cyan-300 bg-cyan-100 px-4 py-1.5 text-sm font-semibold text-cyan-800 shadow-sm dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100'
                        : 'rounded-full px-4 py-1.5 text-sm font-medium text-slate-700 transition dark:text-slate-300';
                }

                if (yearlyButton) {
                    yearlyButton.className = !monthlyActive
                        ? 'rounded-full border border-cyan-300 bg-cyan-100 px-4 py-1.5 text-sm font-semibold text-cyan-800 shadow-sm dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100'
                        : 'rounded-full px-4 py-1.5 text-sm font-medium text-slate-700 transition dark:text-slate-300';
                }
            }

            function setPriceVisibility() {
                root.querySelectorAll('[data-price-plan]').forEach(function (node) {
                    var cycle = node.getAttribute('data-price-cycle');
                    node.classList.toggle('hidden', cycle !== state.billingCycle);
                });

                root.querySelectorAll('[data-yearly-note]').forEach(function (node) {
                    node.classList.toggle('hidden', state.billingCycle !== 'yearly');
                });

                root.querySelectorAll('[data-yearly-save]').forEach(function (node) {
                    node.classList.toggle('hidden', state.billingCycle !== 'yearly');
                });

                root.querySelectorAll('[data-yearly-original-monthly="pro"]').forEach(function (node) {
                    node.classList.toggle('hidden', state.billingCycle !== 'yearly');
                });

                if (savingsBadge) {
                    if (state.billingCycle === 'yearly') {
                        var percent = maxSavingsPercent();
                        savingsBadge.textContent = 'Save ' + percent.toFixed(2) + '%';
                        savingsBadge.classList.remove('hidden');
                    } else {
                        savingsBadge.classList.add('hidden');
                    }
                }
            }

            function setPendingVisibility() {
                if (pagePendingNotice) {
                    pagePendingNotice.classList.toggle('hidden', !state.pending);
                }

                pageUpgradeButtons.forEach(function (button) {
                    if (!button.hasAttribute('data-page-upgrade-trigger')) {
                        return;
                    }

                    if (state.pending) {
                        button.setAttribute('disabled', 'disabled');
                        button.classList.add('opacity-60', 'cursor-not-allowed');
                        button.textContent = 'Upgrade request pending';
                    }
                });
            }

            function syncModalCycle() {
                window.__ismsSubscriptionBillingCycle = state.billingCycle;
            }

            function render() {
                setButtonStates();
                setPriceVisibility();
                setPendingVisibility();
                syncModalCycle();
            }

            monthlyButton?.addEventListener('click', function () {
                state.billingCycle = 'monthly';
                render();
            });

            yearlyButton?.addEventListener('click', function () {
                state.billingCycle = 'yearly';
                render();
            });

            window.addEventListener('isms:subscription-upgrade-submitted', function () {
                state.pending = true;
                render();
            });

            render();
        })();
    </script>
</x-app-layout>
