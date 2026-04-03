<x-app-layout>
    @php
        $sportsColorMap = [
            'basketball' => 'border-orange-300/40 bg-orange-500/20 text-orange-100',
            'volleyball' => 'border-indigo-300/40 bg-indigo-500/20 text-indigo-100',
            'football' => 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100',
            'badminton' => 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100',
        ];

        $statusClasses = [
            'scheduled' => 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100',
            'completed' => 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100',
            'cancelled' => 'border-rose-300/40 bg-rose-500/20 text-rose-100',
        ];

        $sportBadgeClass = static function (?string $sportName) use ($sportsColorMap): string {
            $key = strtolower((string) $sportName);

            foreach ($sportsColorMap as $sportKey => $classes) {
                if (str_contains($key, $sportKey)) {
                    return $classes;
                }
            }

            return 'border-slate-300/40 bg-slate-500/20 text-slate-100';
        };

        $todayDate = now()->toDateString();
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');
        $hasSportsTable = \Illuminate\Support\Facades\Schema::hasTable('sports');
        $hasTeamsTable = \Illuminate\Support\Facades\Schema::hasTable('teams');
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');

        $gamesToday = collect();
        $recentResults = collect();
        $completedGames = collect();
        $topTeamsBySport = collect();

        if ($hasGamesTable) {
            $gamesToday = \App\Models\Game::query()
                ->with(['sport', 'homeTeam', 'awayTeam', 'venue'])
                ->whereDate('scheduled_at', $todayDate)
                ->orderBy('scheduled_at')
                ->get();

            $recentResults = \App\Models\Game::query()
                ->with(['sport', 'homeTeam', 'awayTeam'])
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(5)
                ->get();

            $completedGames = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam'])
                ->where('status', 'completed')
                ->get();
        }

        $totalSports = $hasSportsTable ? \App\Models\Sport::query()->count() : 0;
        $totalTeams = $hasTeamsTable ? \App\Models\Team::query()->count() : 0;
        $totalPlayers = $hasPlayersTable ? \App\Models\Player::query()->count() : 0;

        if ($hasSportsTable && $hasGamesTable) {
            $calculator = app(\App\Support\StandingsCalculator::class);
            $topTeamsBySport = \App\Models\Sport::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function (\App\Models\Sport $sport) use ($completedGames, $calculator): array {
                    $sportGames = $completedGames->where('sport_id', $sport->id)->values();
                    $rows = $calculator->calculate($sportGames);

                    return [
                        'sport' => $sport,
                        'rank_1' => $rows[0]['team'] ?? null,
                        'rank_2' => $rows[1]['team'] ?? null,
                    ];
                })
                ->filter(fn (array $entry): bool => $entry['rank_1'] !== null)
                ->values();
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">School Admin Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Total Sports</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($totalSports) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Total Teams</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($totalTeams) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Total Players</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($totalPlayers) }}</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Games Today</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($gamesToday->count()) }}</p>
            </div>
        </div>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Today's Matches</h3>
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($gamesToday as $game)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sportBadgeClass($game->sport?->name) }}">
                                {{ $game->sport?->name ?? 'Sport' }}
                            </span>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$game->status] ?? 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                {{ strtoupper($game->status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <p class="text-sm font-medium text-slate-100">{{ $game->homeTeam?->name ?? 'TBD Team' }}</p>

                            @if ($game->status === 'completed')
                                <p class="rounded-lg border border-emerald-300/35 bg-emerald-500/20 px-3 py-1 text-sm font-semibold text-emerald-100">
                                    {{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}
                                </p>
                            @else
                                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">VS</p>
                            @endif

                            <p class="text-right text-sm font-medium text-slate-100">{{ $game->awayTeam?->name ?? 'TBD Team' }}</p>
                        </div>

                        <p class="mt-3 text-xs text-slate-400">
                            {{ $game->venue?->name ?? 'No venue' }}
                            @if ($game->status !== 'completed')
                                · {{ $game->scheduled_at?->format('h:i A') }}
                            @endif
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400 lg:col-span-2">
                        No matches scheduled today.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recent Results</h3>
            <div class="grid gap-4 lg:grid-cols-2">
                @forelse ($recentResults as $game)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sportBadgeClass($game->sport?->name) }}">
                                {{ $game->sport?->name ?? 'Sport' }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y') }}</span>
                        </div>
                        <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                            <p class="text-sm text-slate-100">{{ $game->homeTeam?->name ?? 'TBD Team' }}</p>
                            <p class="rounded-lg border border-emerald-300/35 bg-emerald-500/20 px-3 py-1 text-sm font-semibold text-emerald-100">
                                {{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}
                            </p>
                            <p class="text-right text-sm text-slate-100">{{ $game->awayTeam?->name ?? 'TBD Team' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400 lg:col-span-2">
                        No completed matches yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Top Teams by Sport</h3>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($topTeamsBySport as $entry)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-cyan-300">{{ $entry['sport']->name }}</p>
                        <div class="mt-3 space-y-2">
                            <p class="rounded-lg border border-amber-300/35 bg-amber-500/20 px-3 py-2 text-sm text-amber-100">#1 {{ $entry['rank_1'] }}</p>
                            <p class="rounded-lg border border-slate-300/25 bg-white/5 px-3 py-2 text-sm text-slate-200">#2 {{ $entry['rank_2'] ?? 'TBD' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400 md:col-span-2 xl:col-span-3">
                        Top teams will appear once completed games are available.
                    </div>
                @endforelse
            </div>
        </section>

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
