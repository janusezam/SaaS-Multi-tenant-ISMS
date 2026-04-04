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
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-7 {{ $isLocked ? 'pointer-events-none select-none blur-[1px]' : '' }}">
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Sports</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalSports }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Teams</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalTeams }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Games</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $totalGames }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Completed</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $completedGames }}</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Win Rate</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($winRate, 1) }}%</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Most Active Sport</p>
                    <p class="mt-2 text-lg font-semibold text-cyan-200">{{ $mostActiveSport }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $mostActiveSportCount }} games</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Top Scoring Team</p>
                    <p class="mt-2 text-lg font-semibold text-cyan-200">{{ $topScoringTeam?->name ?? 'N/A' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $topScoringPoints }} total points</p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 md:col-span-2 xl:col-span-7">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Match Completion Rate</p>
                        <p class="text-sm font-semibold text-emerald-200">{{ number_format($completionRate, 1) }}%</p>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-950/70">
                        <div class="h-full rounded-full bg-emerald-500/70" style="width: {{ number_format($completionRate, 2, '.', '') }}%"></div>
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
