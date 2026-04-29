<x-app-layout>
    @php
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');

        $playerUser = auth()->user();
        $playerProfile = null;
        $myTeam = null;
        $upcomingMatches = collect();
        $recentResults = collect();
        $nextMatchDate = null;
        $standingRank = null;
        $lastMatchResult = 'N/A';

        if ($hasPlayersTable && $playerUser !== null) {
            $playerProfile = \App\Models\Player::query()
                ->with(['team.sport'])
                ->where('email', $playerUser->email)
                ->first();

            $myTeam = $playerProfile?->team;
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

            $nextMatchDate = $upcomingMatches->first()?->scheduled_at;

            $latestMatch = $recentResults->first();
            if ($latestMatch !== null) {
                $isHome = (int) $latestMatch->home_team_id === (int) $myTeam->id;
                $myScore = $isHome ? (int) ($latestMatch->home_score ?? 0) : (int) ($latestMatch->away_score ?? 0);
                $opponentScore = $isHome ? (int) ($latestMatch->away_score ?? 0) : (int) ($latestMatch->home_score ?? 0);
                $lastMatchResult = $myScore > $opponentScore ? 'Win' : ($myScore < $opponentScore ? 'Loss' : 'Draw');
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
        <h2 class="text-2xl font-semibold text-slate-100">Student Player Dashboard</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <!-- Player Hero -->
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/85 p-8 shadow-2xl">
            <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan-500/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="absolute -inset-2 rounded-full bg-gradient-to-tr from-cyan-500 to-indigo-500 opacity-20 blur"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-full border border-white/20 bg-slate-800 shadow-2xl overflow-hidden">
                            @if ($playerProfile?->photo_path)
                                <img src="{{ tenant_asset($playerProfile->photo_path) }}" class="h-full w-full object-cover" />
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-400">
                            Player Dashboard
                        </span>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-white sm:text-4xl">{{ $playerUser->name }}</h1>
                        <p class="mt-1 text-base text-slate-400">
                            @if ($myTeam)
                                Representing <span class="font-bold text-white">{{ $myTeam->name }}</span> 
                                @if($myTeam->sport) 
                                    in <span class="text-cyan-300">{{ $myTeam->sport->name }}</span>
                                @endif
                            @else
                                Welcome to your intramural hub.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.player.my-schedule') }}" class="group flex items-center gap-2 rounded-2xl bg-cyan-500 px-6 py-3 font-bold text-slate-900 transition-all hover:bg-cyan-400 hover:shadow-lg hover:shadow-cyan-500/25">
                        <span>My Schedule</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-1"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-cyan-500/30">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-500/10 blur-2xl group-hover:bg-cyan-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Next Match</p>
                        <p class="mt-2 text-lg font-bold text-white">{{ $nextMatchDate?->format('M d, h:i A') ?? 'No Games Scheduled' }}</p>
                    </div>
                    <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-emerald-500/30">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Standing</p>
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
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Recent Result</p>
                        <p class="mt-2 text-3xl font-bold text-white tracking-tight">{{ strtoupper($lastMatchResult) }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <h3 class="text-lg font-bold text-white">Upcoming Games</h3>
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
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Match Day</p>
                                <p class="mt-1 text-lg font-bold text-white">vs {{ $opponent ?? 'TBD Team' }}</p>
                            </div>
                            <div class="rounded-lg bg-white/5 p-2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-500"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $game->scheduled_at?->format('h:i A') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $game->venue?->name ?? 'TBD Venue' }}
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center md:col-span-2">
                        <p class="text-sm text-slate-400">No upcoming schedule available.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/><path d="M2 12h20"/><path d="m5 7-3 5 3 5"/><path d="m19 7 3 5-3 5"/></svg>
                <h3 class="text-lg font-bold text-white">Recent Team Results</h3>
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
                                    <p class="text-sm font-bold text-white">
                                        {{ $game->homeTeam?->name }} <span class="text-slate-600">vs</span> {{ $game->awayTeam?->name }}
                                    </p>
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
                        <p class="text-sm text-slate-400">No recent team results yet.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
            For your complete read-only fixtures, open <a href="{{ route('tenant.player.my-schedule') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">My Schedule</a>.
        </div>
    </div>
</x-app-layout>
