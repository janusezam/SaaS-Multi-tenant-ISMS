<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">University Admin Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Control center for university-wide intramural operations.</p>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                <li class="rounded-xl bg-white/5 px-4 py-3">Manage all sports seasons</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Assign facilitators and coaches</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Review standings and analytics</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Approve Pro exports</li>
            </ul>
        </div>
    </div>
</x-app-layout>
