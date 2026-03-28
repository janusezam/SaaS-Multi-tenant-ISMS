<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Pro Analytics</h2>
            @if ($isLocked)
                <span class="rounded-full border border-amber-300/40 bg-amber-500/20 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-100">Locked on Basic</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl">
            <div class="grid gap-4 lg:grid-cols-4 {{ $isLocked ? 'pointer-events-none select-none blur-[1px]' : '' }}">
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Sports</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalSports }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Teams</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalTeams }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Games</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalGames }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Completed</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $completedGames }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 lg:col-span-4">
                    <h3 class="text-lg font-semibold text-slate-100">Pro Insights</h3>
                    <p class="mt-2 text-sm text-slate-300">This panel is available only on the Pro plan and can be extended with trend charts, attendance stats, and sport-specific performance summaries.</p>
                </div>
            </div>

            @if ($isLocked)
                <div class="pro-lock-overlay absolute inset-0 flex items-center justify-center p-6">
                    <div class="pro-lock-card max-w-xl rounded-2xl p-6 text-center">
                        <p class="text-xs uppercase tracking-[0.2em] text-amber-200">Subscription Lock</p>
                        <h3 class="pro-lock-title mt-2 text-xl font-semibold">Upgrade to Pro to unlock Analytics</h3>
                        <p class="pro-lock-copy mt-2 text-sm">You can preview this module, but full analytics access requires a Pro subscription.</p>

                        @if ($canRequestUpgrade)
                            <form method="POST" action="{{ route('tenant.subscription.upgrade.pro') }}" class="mt-4">
                                @csrf
                                <button type="submit" class="rounded-xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-2 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">Request Pro Upgrade</button>
                            </form>
                        @else
                            <p class="pro-lock-note mt-4 text-xs">Ask your university admin to submit the Pro upgrade request.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
