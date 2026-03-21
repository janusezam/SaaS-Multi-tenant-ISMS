<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Sports Facilitator Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Operational board for fixtures, officials, and venue readiness.</p>
            <ul class="mt-4 space-y-2">
                <li class="rounded-xl bg-white/5 px-4 py-3">Today: 12 scheduled matches</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Pending referee confirmations: 3</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Venue conflicts detected: 1</li>
            </ul>
        </div>
    </div>
</x-app-layout>
