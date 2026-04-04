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

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">View your upcoming games, latest team results, and current standing.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your player account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
            </p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Next Match Date</p>
                <p class="mt-2 text-lg font-semibold text-cyan-200">{{ $nextMatchDate?->format('M d, Y h:i A') ?? 'TBD' }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Team Standing Rank</p>
                <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ $standingRank !== null ? '#'.$standingRank : 'N/A' }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Last Match Result</p>
                <p class="mt-2 text-3xl font-semibold text-amber-200">{{ strtoupper($lastMatchResult) }}</p>
            </article>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">My Upcoming Schedule</h3>
            <div class="space-y-3">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">vs {{ $opponent ?? 'TBD Team' }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                            · {{ $game->venue?->name ?? 'No venue assigned' }}
                            · {{ $game->sport?->name ?? 'Sport' }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No upcoming schedule available.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recent Team Results</h3>
            <div class="space-y-3">
                @forelse ($recentResults as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $myScore = $isHome ? (int) ($game->home_score ?? 0) : (int) ($game->away_score ?? 0);
                        $opponentScore = $isHome ? (int) ($game->away_score ?? 0) : (int) ($game->home_score ?? 0);
                        $resultLabel = $myScore > $opponentScore ? 'WIN' : ($myScore < $opponentScore ? 'LOSS' : 'DRAW');
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-100">
                                {{ $game->homeTeam?->name ?? 'TBD Team' }}
                                <span class="px-1 text-slate-400">vs</span>
                                {{ $game->awayTeam?->name ?? 'TBD Team' }}
                            </p>
                            <span class="inline-flex rounded-full border border-emerald-300/35 bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-100">
                                {{ $resultLabel }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-cyan-200">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y') }} · {{ $game->venue?->name ?? 'No venue assigned' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No recent team results yet.
                    </div>
                @endforelse
            </div>
        </section>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
            For your complete read-only fixtures, open <a href="{{ route('tenant.player.my-schedule') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">My Schedule</a>.
        </div>
    </div>
</x-app-layout>
