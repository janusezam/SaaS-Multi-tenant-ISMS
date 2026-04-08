<x-app-layout>
    @php
        $mediaUrl = static function (?string $path): ?string {
            if ($path === null || trim($path) === '') {
                return null;
            }

            $normalized = str_replace('\\', '/', trim($path));

            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $normalized;
            }

            $normalized = ltrim($normalized, '/');
            $normalized = preg_replace('#^(public/)+#', '', $normalized) ?? $normalized;
            $normalized = preg_replace('#^(storage/)+#', '', $normalized) ?? $normalized;

            return tenant_asset($normalized);
        };

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
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">Team Coach Dashboard</h2>
            <p class="mt-1 text-sm text-slate-300">Classroom-inspired stream for your team story this season.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
        <div class="overflow-hidden rounded-2xl border border-cyan-300/25 bg-slate-900/85 text-slate-200">
            <div class="bg-gradient-to-r from-cyan-700/35 via-sky-700/25 to-indigo-700/35 px-6 py-5">
                <p class="text-xs uppercase tracking-[0.16em] text-cyan-200/90">Stream</p>
                <p class="mt-1 text-sm text-cyan-100">View your team's schedule, standing, and recent performance at a glance.</p>
            </div>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="Coach dashboard sections">
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'overview'"
                    :aria-selected="activeTab === 'overview'"
                    :class="activeTab === 'overview' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Overview
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'upcoming'"
                    :aria-selected="activeTab === 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Upcoming
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'results'"
                    :aria-selected="activeTab === 'results'"
                    :class="activeTab === 'results' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Results
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-4" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Upcoming Matches</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($upcomingMatches->count()) }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Team Standing Rank</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ $standingRank !== null ? '#'.$standingRank : 'N/A' }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Team Win/Loss Record</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-200">{{ $wins }} - {{ $losses }}</p>
                </article>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
                For a full read-only history, open <a href="{{ route('tenant.coach.schedules') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Schedules</a>.
            </div>
        </section>

        <section x-show="activeTab === 'upcoming'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">My Team Next Matches</h3>
            <div class="space-y-3">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                        $opponentLogo = $isHome
                            ? $mediaUrl($game->awayTeam?->logo_path)
                            : $mediaUrl($game->homeTeam?->logo_path);
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-100">
                            @if ($opponentLogo !== null)
                                <img src="{{ $opponentLogo }}" alt="{{ $opponent ?? 'TBD Team' }}" class="h-10 w-10 rounded-full border border-white/15 object-cover" />
                            @endif
                            <span>vs {{ $opponent ?? 'TBD Team' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                            · {{ $game->venue?->name ?? 'No venue assigned' }}
                            · {{ $game->sport?->name ?? 'Sport' }}
                        </p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No upcoming matches found.
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'results'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Recent Results (Read-Only)</h3>
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
                            @php
                                $resultHome = $game->homeTeam?->name ?? 'TBD Team';
                                $resultAway = $game->awayTeam?->name ?? 'TBD Team';
                                $resultHomeLogo = $mediaUrl($game->homeTeam?->logo_path);
                                $resultAwayLogo = $mediaUrl($game->awayTeam?->logo_path);
                            @endphp
                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-100">
                                @if ($resultHomeLogo !== null)
                                    <img src="{{ $resultHomeLogo }}" alt="{{ $resultHome }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                                @endif
                                <span>{{ $resultHome }}</span>
                                <span class="px-1 text-slate-400">vs</span>
                                @if ($resultAwayLogo !== null)
                                    <img src="{{ $resultAwayLogo }}" alt="{{ $resultAway }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                                @endif
                                <span>{{ $resultAway }}</span>
                            </div>
                            <span class="inline-flex rounded-full border border-emerald-300/35 bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-100">
                                {{ $resultLabel }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-cyan-200">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y') }} · {{ $game->venue?->name ?? 'No venue assigned' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No recent completed matches found.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
