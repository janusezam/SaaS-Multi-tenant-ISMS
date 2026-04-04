<x-app-layout>
    @php
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');

        $playerUser = auth()->user();
        $playerProfile = null;
        $myTeam = null;
        $upcomingMatches = collect();
        $recentResults = collect();

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
                ->get();

            $recentResults = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(10)
                ->get();
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Schedule</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Read-only schedule and results for your team.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your player account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
            </p>
        </div>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Upcoming Matches</h3>
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
                        No upcoming matches found.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recent Team Results (Read-Only)</h3>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Match</th>
                            <th class="px-4 py-3 text-left font-medium">Score</th>
                            <th class="px-4 py-3 text-left font-medium">Venue</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($recentResults as $game)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $game->homeTeam?->name ?? 'TBD Team' }}
                                    vs
                                    {{ $game->awayTeam?->name ?? 'TBD Team' }}
                                </td>
                                <td class="px-4 py-3 text-cyan-200">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $game->venue?->name ?? 'No venue assigned' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $game->scheduled_at?->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">No completed matches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
