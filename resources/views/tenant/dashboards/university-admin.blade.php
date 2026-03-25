<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">School Admin Dashboard</h2>
            <form method="POST" action="{{ route('tenant.subscription.upgrade.pro') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-amber-300/40 bg-amber-500/20 px-4 py-2 text-sm font-medium text-amber-100 hover:bg-amber-500/30">
                    Upgrade to Pro
                </button>
            </form>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('upgrade_notice'))
            <div class="mb-4 rounded-xl border border-amber-300/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                {{ session('upgrade_notice') }}
            </div>
        @endif

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Control center for school-wide intramural operations.</p>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                <li class="rounded-xl bg-white/5 px-4 py-3">Manage all sports seasons</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Assign facilitators and coaches</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Review standings and analytics</li>
                <li class="rounded-xl bg-white/5 px-4 py-3">Approve Pro exports</li>
            </ul>
        </div>
    </div>
</x-app-layout>
