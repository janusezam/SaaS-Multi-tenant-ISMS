<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Pro Analytics</h2>
    </x-slot>

    <div class="mx-auto grid max-w-7xl gap-4 px-4 py-8 sm:px-6 lg:grid-cols-4 lg:px-8">
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
</x-app-layout>
