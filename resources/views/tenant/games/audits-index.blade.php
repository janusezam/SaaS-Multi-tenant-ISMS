<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Result Audit History</h2>
            <div class="flex items-center gap-2">
                @if (tenant()?->plan === 'pro')
                    <a href="{{ route('tenant.pro.exports.result-audits.csv', request()->query()) }}" class="rounded-xl border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Export CSV</a>
                    <a href="{{ route('tenant.pro.exports.result-audits.pdf', request()->query()) }}" class="rounded-xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-2 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">Export PDF</a>
                @endif
                <a href="{{ route('tenant.games.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">Back to Schedules</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('tenant.audits.game-results.index') }}" class="grid gap-3 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="sport_id" class="mb-1 block text-xs uppercase tracking-wide text-slate-400">Sport</label>
                <select id="sport_id" name="sport_id" class="w-full rounded-md border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100">
                    <option value="">All Sports</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected(($filters['sport_id'] ?? null) === $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="changed_by_user_id" class="mb-1 block text-xs uppercase tracking-wide text-slate-400">Changed By User ID</label>
                <input id="changed_by_user_id" type="number" min="1" name="changed_by_user_id" value="{{ $filters['changed_by_user_id'] ?? '' }}" class="w-full rounded-md border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100" placeholder="Any user" />
            </div>

            <div>
                <label for="from_date" class="mb-1 block text-xs uppercase tracking-wide text-slate-400">From</label>
                <input id="from_date" type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded-md border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100" />
            </div>

            <div>
                <label for="to_date" class="mb-1 block text-xs uppercase tracking-wide text-slate-400">To</label>
                <input id="to_date" type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded-md border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100" />
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-md border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Filter</button>
                <a href="{{ route('tenant.audits.game-results.index') }}" class="rounded-md border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</a>
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
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($audits as $audit)
                        <tr>
                            <td class="px-4 py-3">{{ $audit->created_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">{{ $audit->game?->sport?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $audit->game?->homeTeam?->name ?? '-' }} vs {{ $audit->game?->awayTeam?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $audit->changed_by_user_id ?? 'System' }}</td>
                            <td class="px-4 py-3">{{ strtoupper($audit->previous_status) }} <span class="mx-1 text-slate-500">-></span> {{ strtoupper($audit->new_status) }}</td>
                            <td class="px-4 py-3">
                                {{ $audit->previous_home_score ?? '-' }}-{{ $audit->previous_away_score ?? '-' }}
                                <span class="mx-1 text-slate-500">-></span>
                                {{ $audit->new_home_score ?? '-' }}-{{ $audit->new_away_score ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">No audit records found for the selected filters.</td>
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
