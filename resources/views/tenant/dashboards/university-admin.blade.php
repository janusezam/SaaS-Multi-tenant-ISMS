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

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <!-- Stats Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:scale-[1.02] hover:border-cyan-500/30 hover:shadow-xl hover:shadow-cyan-500/10">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-500/10 blur-2xl group-hover:bg-cyan-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Sports</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ number_format($totalSports) }}</p>
                    </div>
                    <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:scale-[1.02] hover:border-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/10">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-indigo-500/10 blur-2xl group-hover:bg-indigo-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Teams</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ number_format($totalTeams) }}</p>
                    </div>
                    <div class="rounded-xl bg-indigo-500/10 p-3 text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:scale-[1.02] hover:border-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Players</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ number_format($totalPlayers) }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M15 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:scale-[1.02] hover:border-amber-500/30 hover:shadow-xl hover:shadow-amber-500/10">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl group-hover:bg-amber-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Games Today</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ number_format($gamesToday->count()) }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                <h3 class="text-lg font-bold text-white">Today's Matches</h3>
            </div>
            <div class="grid gap-6 lg:grid-cols-2">
                @forelse ($gamesToday as $game)
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $sportBadgeClass($game->sport?->name) }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $game->sport?->name ?? 'Sport' }}
                            </span>
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wider {{ $statusClasses[$game->status] ?? 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                {{ $game->status }}
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                            <div class="text-center">
                                <p class="text-base font-bold text-white">{{ $game->homeTeam?->name ?? 'TBD Team' }}</p>
                                <p class="text-[10px] uppercase tracking-widest text-slate-500 mt-1">Home</p>
                            </div>

                            <div class="flex flex-col items-center">
                                @if ($game->status === 'completed')
                                    <div class="flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 font-mono text-xl font-black text-emerald-400 shadow-inner shadow-emerald-500/10">
                                        {{ $game->home_score ?? 0 }} <span class="text-slate-600">:</span> {{ $game->away_score ?? 0 }}
                                    </div>
                                @else
                                    <div class="h-10 w-10 flex items-center justify-center rounded-full border border-white/5 bg-white/5 text-[10px] font-black text-slate-500">
                                        VS
                                    </div>
                                @endif
                            </div>

                            <div class="text-center">
                                <p class="text-base font-bold text-white">{{ $game->awayTeam?->name ?? 'TBD Team' }}</p>
                                <p class="text-[10px] uppercase tracking-widest text-slate-500 mt-1">Away</p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-4">
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>{{ $game->venue?->name ?? 'No venue' }}</span>
                            </div>
                            @if ($game->status !== 'completed')
                                <div class="flex items-center gap-2 text-xs font-medium text-cyan-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span>{{ $game->scheduled_at?->format('h:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center lg:col-span-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 mb-4"><path d="m8 2 1.88 1.88"/><path d="M14.12 3.88 16 2"/><path d="M9 7.13v-1"/><path d="M15 7.13v-1"/><path d="M22 12v-2c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v2"/><path d="M2 12v.2a2 2 0 0 0 .5 1.3l1.5 2.5v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3l1.5-2.5a2 2 0 0 0 .5-1.3V12h-20Z"/></svg>
                        <p class="text-slate-400">No matches scheduled today.</p>
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

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="m6 9 6 6 6-6"/><path d="M12 3v12"/></svg>
                <h3 class="text-lg font-bold text-white">Top Teams by Sport</h3>
            </div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($topTeamsBySport as $entry)
                    <article class="group relative rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all duration-300 hover:border-amber-500/30">
                        <div class="flex items-center justify-between border-b border-white/5 pb-3">
                            <p class="text-xs font-black uppercase tracking-widest text-cyan-400">{{ $entry['sport']->name }}</p>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="relative flex items-center justify-between rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-3 shadow-inner transition-all group-hover:bg-amber-500/10">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-slate-900 shadow-lg shadow-amber-500/40">1</span>
                                    <p class="text-sm font-bold text-amber-100">{{ $entry['rank_1'] }}</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500"><path d="M6 3h12l4 6-10 13L2 9l4-6Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/></svg>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 px-4 py-3 transition-all group-hover:bg-white/10">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-700 text-[10px] font-black text-slate-300">2</span>
                                <p class="text-sm font-medium text-slate-300">{{ $entry['rank_2'] ?? 'TBD' }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center md:col-span-2 xl:col-span-3">
                        <p class="text-slate-400">Top teams will appear once completed games are available.</p>
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
