<x-app-layout>
    @php
        $games = \App\Models\Game::query()->with(['sport', 'homeTeam', 'awayTeam'])->get();
        $completedMatches = $games->where('status', 'completed')->values();

        $drawCount = $completedMatches->filter(function ($game): bool {
            return $game->home_score !== null && $game->away_score !== null && (int) $game->home_score === (int) $game->away_score;
        })->count();

        $winRate = $completedMatches->count() > 0
            ? (($completedMatches->count() - $drawCount) / $completedMatches->count()) * 100
            : 0;

        $gamesPerSport = $games
            ->groupBy(fn ($game): string => $game->sport?->name ?? 'Unknown')
            ->map(fn ($items): int => $items->count())
            ->sortDesc();

        $mostActiveSport = $gamesPerSport->keys()->first() ?? 'N/A';
        $mostActiveSportCount = (int) ($gamesPerSport->first() ?? 0);
        $maxGamesPerSport = max(1, (int) ($gamesPerSport->max() ?? 1));

        $completionRate = $totalGames > 0 ? ($completedGames / $totalGames) * 100 : 0;

        $statusDistribution = [
            'Scheduled' => $games->where('status', 'scheduled')->count(),
            'Completed' => $games->where('status', 'completed')->count(),
            'Cancelled' => $games->where('status', 'cancelled')->count(),
        ];

        $trendLabels = [];
        $trendScheduled = [];
        $trendCompleted = [];

        foreach (range(6, 0) as $dayOffset) {
            $date = now()->subDays($dayOffset)->toDateString();
            $label = now()->subDays($dayOffset)->format('M d');

            $trendLabels[] = $label;
            $trendScheduled[] = $games->filter(fn ($game): bool => $game->scheduled_at?->toDateString() === $date)->count();
            $trendCompleted[] = $completedMatches->filter(fn ($game): bool => $game->scheduled_at?->toDateString() === $date)->count();
        }

        $teamScoring = [];
        $teamWinsLosses = [];

        foreach ($completedMatches as $game) {
            if ($game->home_team_id !== null) {
                $teamScoring[$game->home_team_id] = ($teamScoring[$game->home_team_id] ?? 0) + (int) ($game->home_score ?? 0);
            }

            if ($game->away_team_id !== null) {
                $teamScoring[$game->away_team_id] = ($teamScoring[$game->away_team_id] ?? 0) + (int) ($game->away_score ?? 0);
            }

            if ($game->home_score === null || $game->away_score === null || $game->home_score === $game->away_score) {
                continue;
            }

            $winnerId = $game->home_score > $game->away_score ? $game->home_team_id : $game->away_team_id;
            $loserId = $game->home_score > $game->away_score ? $game->away_team_id : $game->home_team_id;

            if ($winnerId !== null) {
                $teamWinsLosses[$winnerId]['wins'] = ($teamWinsLosses[$winnerId]['wins'] ?? 0) + 1;
                $teamWinsLosses[$winnerId]['losses'] = $teamWinsLosses[$winnerId]['losses'] ?? 0;
            }

            if ($loserId !== null) {
                $teamWinsLosses[$loserId]['losses'] = ($teamWinsLosses[$loserId]['losses'] ?? 0) + 1;
                $teamWinsLosses[$loserId]['wins'] = $teamWinsLosses[$loserId]['wins'] ?? 0;
            }
        }

        $topScoringTeamId = collect($teamScoring)->sortDesc()->keys()->first();
        $topScoringTeam = $topScoringTeamId
            ? $completedMatches
                ->flatMap(fn ($game) => [$game->homeTeam, $game->awayTeam])
                ->filter()
                ->firstWhere('id', (int) $topScoringTeamId)
            : null;
        $topScoringPoints = $topScoringTeamId !== null ? (int) ($teamScoring[$topScoringTeamId] ?? 0) : 0;

        $topPerformingTeams = collect($teamWinsLosses)
            ->map(function (array $stats, int $teamId) use ($completedMatches): array {
                $team = $completedMatches
                    ->flatMap(fn ($game) => [$game->homeTeam, $game->awayTeam])
                    ->filter()
                    ->firstWhere('id', $teamId);

                $wins = (int) ($stats['wins'] ?? 0);
                $losses = (int) ($stats['losses'] ?? 0);
                $played = $wins + $losses;

                return [
                    'team' => $team?->name ?? 'Unknown Team',
                    'sport' => $team?->sport?->name ?? 'Unknown Sport',
                    'wins' => $wins,
                    'losses' => $losses,
                    'win_rate' => $played > 0 ? ($wins / $played) * 100 : 0,
                ];
            })
            ->sortByDesc('win_rate')
            ->take(5)
            ->values();

            $topTeamsLabels = $topPerformingTeams->pluck('team')->values();
            $topTeamsWinRates = $topPerformingTeams->pluck('win_rate')->map(fn ($value): float => round((float) $value, 1))->values();
            $topTeamsWins = $topPerformingTeams->pluck('wins')->values();
            $topTeamsLosses = $topPerformingTeams->pluck('losses')->values();
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Pro Analytics</h2>
            @if ($isLocked)
                <span class="rounded-full border border-amber-300/40 bg-amber-500/20 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-100">Locked on Basic</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl space-y-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 {{ $isLocked ? 'pointer-events-none select-none blur-[1px]' : '' }}">
                {{-- Sports Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Sports</p>
                        <div class="rounded-lg bg-cyan-500/10 p-1.5 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-100">{{ $totalSports }}</p>
                </div>

                {{-- Teams Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Teams</p>
                        <div class="rounded-lg bg-cyan-500/10 p-1.5 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-100">{{ $totalTeams }}</p>
                </div>

                {{-- Games Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Games</p>
                        <div class="rounded-lg bg-cyan-500/10 p-1.5 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-100">{{ $totalGames }}</p>
                </div>

                {{-- Completed Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Completed</p>
                        <div class="rounded-lg bg-emerald-500/10 p-1.5 text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-100">{{ $completedGames }}</p>
                </div>

                {{-- Win Rate Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Win Rate</p>
                        <div class="rounded-lg bg-amber-500/10 p-1.5 text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-100">{{ number_format($winRate, 1) }}%</p>
                </div>

                {{-- Most Active Sport Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-indigo-500/30 hover:shadow-lg hover:shadow-indigo-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.99 7.99 0 0120 13a7.989 7.989 0 01-2.343 5.657z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Most Active Sport</p>
                        <div class="rounded-lg bg-indigo-500/10 p-1.5 text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.99 7.99 0 0120 13a7.989 7.989 0 01-2.343 5.657z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-lg font-bold text-slate-100 truncate">{{ $mostActiveSport }}</p>
                    <p class="mt-1 text-[10px] font-medium text-slate-400 uppercase tracking-widest">{{ $mostActiveSportCount }} games</p>
                </div>

                {{-- Top Scoring Team Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:scale-[1.02] hover:border-violet-500/30 hover:shadow-lg hover:shadow-violet-900/10">
                    <div class="absolute -right-2 -top-2 opacity-5 transition-transform group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Top Scoring Team</p>
                        <div class="rounded-lg bg-violet-500/10 p-1.5 text-violet-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-lg font-bold text-slate-100 truncate">{{ $topScoringTeam?->name ?? 'N/A' }}</p>
                    <p class="mt-1 text-[10px] font-medium text-slate-400 uppercase tracking-widest">{{ $topScoringPoints }} pts total</p>
                </div>

                {{-- Match Completion Rate Card --}}
                <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 md:col-span-2 xl:col-span-7 transition-all hover:border-emerald-500/20">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-emerald-500/10 p-1.5 text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Match Completion Rate</p>
                        </div>
                        <p class="text-sm font-black text-emerald-400">{{ number_format($completionRate, 1) }}%</p>
                    </div>
                    <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-950/70 shadow-inner">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-emerald-400 transition-all duration-1000 ease-out shadow-[0_0_12px_rgba(16,185,129,0.4)]" style="width: {{ number_format($completionRate, 2, '.', '') }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 xl:col-span-7">
                    <h3 class="text-lg font-semibold text-slate-100">Top Performing Teams (Horizontal)</h3>
                    <div class="mt-4 h-80">
                        <canvas id="analytics-top-teams-horizontal"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 xl:col-span-4">
                    <h3 class="text-lg font-semibold text-slate-100">Games per Sport (Vertical Bar)</h3>
                    <div class="mt-4 h-72">
                        <canvas id="analytics-games-per-sport-bar"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 xl:col-span-3">
                    <h3 class="text-lg font-semibold text-slate-100">Status Distribution (Doughnut)</h3>
                    <div class="mt-4 h-72">
                        <canvas id="analytics-status-doughnut"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 xl:col-span-3">
                    <h3 class="text-lg font-semibold text-slate-100">Sport Share (Pie)</h3>
                    <div class="mt-4 h-72">
                        <canvas id="analytics-sport-share-pie"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 xl:col-span-4">
                    <h3 class="text-lg font-semibold text-slate-100">7-Day Match Trend (Line)</h3>
                    <div class="mt-4 h-72">
                        <canvas id="analytics-trend-line"></canvas>
                    </div>
                </div>
            </div>

            @if ($isLocked)
                <div class="pro-lock-overlay absolute inset-0 flex items-center justify-center p-6">
                    <div class="pro-lock-card max-w-xl rounded-2xl p-6 text-center">
                        <p class="text-xs uppercase tracking-[0.2em] text-amber-200">Subscription Lock</p>
                        <h3 class="pro-lock-title mt-2 text-xl font-semibold">Upgrade to Pro to unlock Analytics</h3>
                        <p class="pro-lock-copy mt-2 text-sm">You can preview this module, but full analytics access requires a Pro subscription.</p>
                        <button type="button" data-upgrade-trigger class="mt-4 rounded-xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-2 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">
                            Request Upgrade
                        </button>
                        <p class="pro-lock-note mt-3 text-xs">Pro access is managed by central subscription settings.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const chartsRoot = document.documentElement;

            const gamesPerSportLabels = @json($gamesPerSport->keys()->values());
            const gamesPerSportData = @json($gamesPerSport->values());
            const statusLabels = @json(array_keys($statusDistribution));
            const statusData = @json(array_values($statusDistribution));
            const trendLabels = @json($trendLabels);
            const trendScheduled = @json($trendScheduled);
            const trendCompleted = @json($trendCompleted);
            const topTeamsLabels = @json($topTeamsLabels);
            const topTeamsWinRates = @json($topTeamsWinRates);
            const topTeamsWins = @json($topTeamsWins);
            const topTeamsLosses = @json($topTeamsLosses);

            if (typeof Chart === 'undefined') {
                return;
            }

            const chartInstances = [];

            const destroyCharts = () => {
                chartInstances.forEach((chart) => chart.destroy());
                chartInstances.length = 0;
            };

            const renderCharts = () => {
                destroyCharts();

                const isLight = chartsRoot.getAttribute('data-theme') === 'light';
                const tickColor = isLight ? '#334155' : '#cbd5e1';
                const gridColor = isLight ? 'rgba(15, 23, 42, 0.12)' : 'rgba(148, 163, 184, 0.2)';
                const chartDefaults = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: tickColor,
                            },
                        },
                    },
                };

                const barCanvas = document.getElementById('analytics-games-per-sport-bar');
                if (barCanvas) {
                    chartInstances.push(new Chart(barCanvas, {
                        type: 'bar',
                        data: {
                            labels: gamesPerSportLabels,
                            datasets: [{
                                label: 'Games',
                                data: gamesPerSportData,
                                backgroundColor: 'rgba(34, 211, 238, 0.6)',
                                borderColor: 'rgba(34, 211, 238, 1)',
                                borderWidth: 1,
                                borderRadius: 8,
                            }],
                        },
                        options: {
                            ...chartDefaults,
                            indexAxis: 'x',
                            scales: {
                                x: {
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                            },
                        },
                    }));
                }

                const topTeamsCanvas = document.getElementById('analytics-top-teams-horizontal');
                if (topTeamsCanvas) {
                    chartInstances.push(new Chart(topTeamsCanvas, {
                        type: 'bar',
                        data: {
                            labels: topTeamsLabels,
                            datasets: [
                                {
                                    label: 'Win Rate %',
                                    data: topTeamsWinRates,
                                    backgroundColor: 'rgba(34, 211, 238, 0.65)',
                                    borderColor: 'rgba(34, 211, 238, 1)',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Wins',
                                    data: topTeamsWins,
                                    backgroundColor: 'rgba(16, 185, 129, 0.45)',
                                    borderColor: 'rgba(16, 185, 129, 1)',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Losses',
                                    data: topTeamsLosses,
                                    backgroundColor: 'rgba(244, 63, 94, 0.45)',
                                    borderColor: 'rgba(244, 63, 94, 1)',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                            ],
                        },
                        options: {
                            ...chartDefaults,
                            indexAxis: 'y',
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                                y: {
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                            },
                        },
                    }));
                }

                const doughnutCanvas = document.getElementById('analytics-status-doughnut');
                if (doughnutCanvas) {
                    chartInstances.push(new Chart(doughnutCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusData,
                                backgroundColor: [
                                    'rgba(56, 189, 248, 0.75)',
                                    'rgba(16, 185, 129, 0.75)',
                                    'rgba(244, 63, 94, 0.75)',
                                ],
                                borderColor: [
                                    'rgba(56, 189, 248, 1)',
                                    'rgba(16, 185, 129, 1)',
                                    'rgba(244, 63, 94, 1)',
                                ],
                                borderWidth: 1,
                            }],
                        },
                        options: chartDefaults,
                    }));
                }

                const pieCanvas = document.getElementById('analytics-sport-share-pie');
                if (pieCanvas) {
                    chartInstances.push(new Chart(pieCanvas, {
                        type: 'pie',
                        data: {
                            labels: gamesPerSportLabels,
                            datasets: [{
                                data: gamesPerSportData,
                                backgroundColor: [
                                    'rgba(14, 165, 233, 0.78)',
                                    'rgba(59, 130, 246, 0.78)',
                                    'rgba(99, 102, 241, 0.78)',
                                    'rgba(16, 185, 129, 0.78)',
                                    'rgba(245, 158, 11, 0.78)',
                                    'rgba(244, 63, 94, 0.78)',
                                ],
                                borderColor: 'rgba(15, 23, 42, 0.2)',
                                borderWidth: 1,
                            }],
                        },
                        options: chartDefaults,
                    }));
                }

                const lineCanvas = document.getElementById('analytics-trend-line');
                if (lineCanvas) {
                    chartInstances.push(new Chart(lineCanvas, {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [
                                {
                                    label: 'Scheduled',
                                    data: trendScheduled,
                                    borderColor: 'rgba(34, 211, 238, 1)',
                                    backgroundColor: 'rgba(34, 211, 238, 0.25)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3,
                                },
                                {
                                    label: 'Completed',
                                    data: trendCompleted,
                                    borderColor: 'rgba(16, 185, 129, 1)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.18)',
                                    fill: true,
                                    tension: 0.35,
                                    pointRadius: 3,
                                },
                            ],
                        },
                        options: {
                            ...chartDefaults,
                            scales: {
                                x: {
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor },
                                },
                            },
                        },
                    }));
                }
            };

            renderCharts();

            const observer = new MutationObserver((mutations) => {
                const themeChanged = mutations.some((mutation) => mutation.attributeName === 'data-theme');

                if (themeChanged) {
                    renderCharts();
                }
            });

            observer.observe(chartsRoot, {
                attributes: true,
                attributeFilter: ['data-theme'],
            });
        })();
    </script>
</x-app-layout>
