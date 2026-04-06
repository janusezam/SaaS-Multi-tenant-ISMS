<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Bracket Result Audits</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-1">
            <a href="{{ route('tenant.pro.bracket', ['sport_id' => $selectedSportId]) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">Back to Bracket</a>
        </div>

        <form method="GET" action="{{ route('tenant.pro.bracket.audits') }}" class="grid gap-3 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-3">
            <div>
                <label for="sport_id" class="mb-1 block text-xs uppercase tracking-wide text-slate-400">Sport</label>
                <select id="sport_id" name="sport_id" class="w-full rounded-md border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100">
                    <option value="">All Sports</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected((string) $selectedSportId === (string) $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-md border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Filter</button>
                <a href="{{ route('tenant.pro.bracket.audits') }}" class="rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Changed At</th>
                        <th class="px-4 py-3 text-left font-medium">Sport</th>
                        <th class="px-4 py-3 text-left font-medium">Match</th>
                        <th class="px-4 py-3 text-left font-medium">Changed By</th>
                        <th class="px-4 py-3 text-left font-medium">Winner Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($audits as $audit)
                        <tr>
                            <td class="px-4 py-3">{{ $audit->created_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">{{ $audit->bracketMatch?->sport?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $audit->bracketMatch?->homeTeam?->name ?? ($audit->bracketMatch?->home_slot_label ?? 'TBD') }} vs {{ $audit->bracketMatch?->awayTeam?->name ?? ($audit->bracketMatch?->away_slot_label ?? 'TBD') }}</td>
                            <td class="px-4 py-3">{{ $audit->changed_by_user_id ?? 'System' }}</td>
                            <td class="px-4 py-3">{{ $audit->previousWinnerTeam?->name ?? 'None' }} <span class="mx-1 text-slate-500">-></span> {{ $audit->newWinnerTeam?->name ?? 'None' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No bracket result changes recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $audits->links() }}
        </div>
    </div>
</x-app-layout>
