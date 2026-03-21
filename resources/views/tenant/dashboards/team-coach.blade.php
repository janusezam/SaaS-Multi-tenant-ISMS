<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Team Coach Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Manage your roster, game schedules, and performance reports.</p>
            <ul class="mt-4 space-y-2">
                <li class="rounded-xl bg-white/5 px-4 py-3">Upcoming training sessions: 4</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Matches this week: 2</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Players requiring clearance: 1</li>
            </ul>
        </div>
    </div>
</x-app-layout>
