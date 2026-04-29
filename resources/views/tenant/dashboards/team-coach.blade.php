<x-app-layout>
    @php
        $hasTeamsTable = \Illuminate\Support\Facades\Schema::hasTable('teams');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');

        $coachUser = auth()->user();
        $myTeam = null;
        $upcomingMatches = collect();
        $recentResults = collect();
        $standingRank = null;
        $wins = 0;
        $losses = 0;

        if ($hasTeamsTable && $coachUser !== null) {
            $myTeam = \App\Models\Team::query()
                ->with('sport')
                ->where('coach_email', $coachUser->email)
                ->first();
        }

        if ($hasGamesTable && $myTeam !== null) {
            $upcomingMatches = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();

            $recentResults = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(5)
                ->get();

            foreach ($recentResults as $game) {
                $isHome = (int) $game->home_team_id === (int) $myTeam->id;
                $myScore = $isHome ? (int) ($game->home_score ?? 0) : (int) ($game->away_score ?? 0);
                $opponentScore = $isHome ? (int) ($game->away_score ?? 0) : (int) ($game->home_score ?? 0);

                if ($myScore > $opponentScore) {
                    $wins++;
                } elseif ($myScore < $opponentScore) {
                    $losses++;
                }
            }

            if ($myTeam->sport_id !== null) {
                $completedSportGames = \App\Models\Game::query()
                    ->with(['homeTeam', 'awayTeam'])
                    ->where('sport_id', $myTeam->sport_id)
                    ->where('status', 'completed')
                    ->get();

                $standingsRows = app(\App\Support\StandingsCalculator::class)->calculate($completedSportGames);

                foreach ($standingsRows as $index => $row) {
                    if (($row['team'] ?? null) === $myTeam->name) {
                        $standingRank = $index + 1;
                        break;
                    }
                }
            }
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Team Coach Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <!-- Team Banner -->
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/85 p-8 shadow-2xl">
            <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="absolute -inset-2 rounded-full bg-gradient-to-tr from-cyan-500 to-indigo-500 opacity-20 blur"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-full border border-white/20 bg-slate-800 shadow-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-400">
                            Team Management
                        </span>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-white sm:text-4xl">{{ $myTeam?->name ?? 'No Team Linked' }}</h1>
                        <p class="mt-1 text-base text-slate-400">
                            @if ($myTeam?->sport?->name)
                                <span class="font-bold text-cyan-300">{{ $myTeam->sport->name }}</span> Season Hub
                            @else
                                Assign a team to your account to see analytics.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.coach.my-team') }}" class="group flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-6 py-3 font-bold text-white transition-all hover:bg-white/10">
                        <span>My Team</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 transition-transform group-hover:translate-x-1"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-cyan-500/30">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-500/10 blur-2xl group-hover:bg-cyan-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Upcoming Matches</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ number_format($upcomingMatches->count()) }}</p>
                    </div>
                    <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-emerald-500/30">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Standing Rank</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ $standingRank !== null ? '#'.$standingRank : 'N/A' }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-amber-500/30">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl group-hover:bg-amber-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Win/Loss Record</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ $wins }} - {{ $losses }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <h3 class="text-lg font-bold text-white">Next Matches</h3>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                    @endphp
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:border-cyan-500/30">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Upcoming</p>
                                <p class="mt-1 text-lg font-bold text-white">vs {{ $opponent ?? 'TBD Team' }}</p>
                            </div>
                            <div class="rounded-lg bg-white/5 p-2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $game->scheduled_at?->format('h:i A') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-500"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $game->venue?->name ?? 'TBD Venue' }}
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center md:col-span-2">
                        <p class="text-sm text-slate-400">No upcoming matches found.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m5 7-3 5 3 5"/><path d="m19 7 3 5-3 5"/></svg>
                <h3 class="text-lg font-bold text-white">Recent Performance</h3>
            </div>
            <div class="space-y-4">
                @forelse ($recentResults as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $myScore = $isHome ? (int) ($game->home_score ?? 0) : (int) ($game->away_score ?? 0);
                        $opponentScore = $isHome ? (int) ($game->away_score ?? 0) : (int) ($game->home_score ?? 0);
                        $resultLabel = $myScore > $opponentScore ? 'WIN' : ($myScore < $opponentScore ? 'LOSS' : 'DRAW');
                        $resultColor = $myScore > $opponentScore ? 'emerald' : ($myScore < $opponentScore ? 'rose' : 'slate');
                    @endphp
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 transition-all hover:border-{{ $resultColor }}-500/30">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-{{ $resultColor }}-500/30 bg-{{ $resultColor }}-500/10 font-black text-{{ $resultColor }}-400">
                                    {{ substr($resultLabel, 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-bold text-white">
                                            {{ $game->homeTeam?->name }} <span class="text-slate-600">vs</span> {{ $game->awayTeam?->name }}
                                        </p>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">{{ $game->scheduled_at?->format('M d, Y') }} · {{ $game->sport?->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-black text-white tracking-tighter">
                                    <span class="{{ $isHome ? 'text-cyan-400' : 'text-slate-400' }}">{{ $game->home_score ?? 0 }}</span>
                                    <span class="text-slate-600 mx-1">-</span>
                                    <span class="{{ !$isHome ? 'text-cyan-400' : 'text-slate-400' }}">{{ $game->away_score ?? 0 }}</span>
                                </p>
                                <p class="text-[10px] font-black uppercase tracking-widest text-{{ $resultColor }}-500">{{ $resultLabel }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center">
                        <p class="text-sm text-slate-400">No recent completed matches found.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
            For a full read-only history, open <a href="{{ route('tenant.coach.schedules') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Schedules</a>.
        </div>
    </div>
</x-app-layout>
