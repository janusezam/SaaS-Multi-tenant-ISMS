<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Edit Scheduled Game</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            <form method="POST" action="{{ route('tenant.games.update', $game) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('tenant.games.partials.form-fields', ['game' => $game])
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Update</button>
                    <a href="{{ route('tenant.games.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
