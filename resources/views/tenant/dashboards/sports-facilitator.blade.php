<x-app-layout>
    @php
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');
        $hasAuditsTable = \Illuminate\Support\Facades\Schema::hasTable('game_result_audits');
        $today = now()->toDateString();
        $userId = auth()->id();

        $gamesScheduledToday = collect();
        $pendingResultReports = 0;
        $awaitingAuditConfirmation = 0;
        $submittedReports = collect();

        if ($hasGamesTable) {
            $gamesScheduledToday = \App\Models\Game::query()
                ->with(['sport', 'homeTeam', 'awayTeam', 'venue'])
                ->whereDate('scheduled_at', $today)
                ->orderBy('scheduled_at')
                ->get();

            $pendingResultReports = \App\Models\Game::query()
                ->where('status', 'scheduled')
                ->where('scheduled_at', '<', now())
                ->count();
        }

        if ($hasAuditsTable) {
            $awaitingAuditConfirmation = \App\Models\GameResultAudit::query()
                ->where('changed_by_user_id', $userId)
                ->where('new_status', 'completed')
                ->count();

            $submittedReports = \App\Models\GameResultAudit::query()
                ->with(['game.sport', 'game.homeTeam', 'game.awayTeam'])
                ->where('changed_by_user_id', $userId)
                ->latest()
                ->limit(8)
                ->get();
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Sports Facilitator Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Manage and report games assigned to your sports.</p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Games Scheduled Today</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($gamesScheduledToday->count()) }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Pending Result Reports</p>
                <p class="mt-2 text-3xl font-semibold text-amber-200">{{ number_format($pendingResultReports) }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Awaiting Audit Confirmation</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ number_format($awaitingAuditConfirmation) }}</p>
            </article>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Today's Assigned Matches</h3>
            <div class="space-y-3">
                @forelse ($gamesScheduledToday as $game)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-100">
                                    {{ $game->homeTeam?->name ?? 'TBD Team' }}
                                    <span class="px-1 text-slate-400">vs</span>
                                    {{ $game->awayTeam?->name ?? 'TBD Team' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $game->sport?->name ?? 'Sport' }}
                                    · {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                                    · {{ $game->venue?->name ?? 'No venue assigned' }}
                                </p>
                            </div>

                            <a
                                href="{{ route('tenant.games.index') }}"
                                class="inline-flex items-center rounded-lg border border-cyan-300/35 bg-cyan-500/20 px-3 py-1.5 text-xs font-semibold text-cyan-100 transition hover:bg-cyan-500/30"
                            >
                                Report Result
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No assigned matches for today.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recently Submitted Reports</h3>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Match</th>
                            <th class="px-4 py-3 text-left font-medium">Sport</th>
                            <th class="px-4 py-3 text-left font-medium">Submitted Status</th>
                            <th class="px-4 py-3 text-left font-medium">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($submittedReports as $report)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $report->game?->homeTeam?->name ?? 'TBD Team' }}
                                    vs
                                    {{ $report->game?->awayTeam?->name ?? 'TBD Team' }}
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $report->game?->sport?->name ?? 'Sport' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-emerald-300/35 bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-100">
                                        {{ strtoupper($report->new_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $report->created_at?->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">
                                    No submitted reports yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
            Use <a href="{{ route('tenant.games.index') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Schedules</a> to update game outcomes and
            <a href="{{ route('tenant.audits.game-results.index') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Result Audits</a> to verify changes.
        </div>
    </div>
</x-app-layout>
