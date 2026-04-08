<x-app-layout>
    @php
        $teamLogoUrl = static function (?string $path): ?string {
            if ($path === null || trim($path) === '') {
                return null;
            }

            $normalizedPath = str_replace('\\', '/', trim($path));

            if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
                return $normalizedPath;
            }

            $normalizedPath = ltrim($normalizedPath, '/');
            $normalizedPath = preg_replace('#^(public/)+#', '', $normalizedPath) ?? $normalizedPath;
            $normalizedPath = preg_replace('#^(storage/)+#', '', $normalizedPath) ?? $normalizedPath;

            return tenant_asset($normalizedPath);
        };

        $hasTeamsTable = \Illuminate\Support\Facades\Schema::hasTable('teams');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');

        $coachUser = auth()->user();
        $myTeam = null;
        $upcomingMatches = collect();
        $completedMatches = collect();
        $nextMatch = null;

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
                ->get();

            $completedMatches = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue', 'sport'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(10)
                ->get();

            $nextMatch = $upcomingMatches->first();
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Team Schedules</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Use this timeline to prepare lineups and confirm team participation.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
            </p>
            <p class="mt-2 text-sm text-cyan-100">Manage player assignments in <a href="{{ route('tenant.coach.my-team') }}" class="font-semibold underline underline-offset-2">My Team</a>.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="Coach schedules sections">
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
                    @click="activeTab = 'completed'"
                    :aria-selected="activeTab === 'completed'"
                    :class="activeTab === 'completed' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Completed
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-4" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Upcoming Matches</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $upcomingMatches->count() }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Completed Matches</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ $completedMatches->count() }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Next Match</p>
                    <p class="mt-2 text-sm font-semibold text-amber-200">{{ $nextMatch?->scheduled_at?->format('M d, Y h:i A') ?? 'No upcoming match' }}</p>
                </article>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-slate-400">Next Action</p>
                <div class="mt-3 text-sm">
                    <a href="{{ route('tenant.coach.my-team') }}" class="inline-flex rounded-lg border border-amber-300/35 bg-amber-500/20 px-3 py-1.5 font-semibold text-amber-100 hover:bg-amber-500/30">Go To My Team Actions</a>
                </div>
            </div>
        </section>

        <section x-show="activeTab === 'upcoming'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Upcoming Matches</h3>
            <p class="text-sm text-slate-400">Review schedule timing and venue before you set lineup in My Team.</p>
            <div class="space-y-3">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-100">
                            @php
                                $opponentTeam = $isHome ? $game->awayTeam : $game->homeTeam;
                                $opponentLogo = $teamLogoUrl($opponentTeam?->logo_path);
                            @endphp
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

        <section x-show="activeTab === 'completed'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Completed Matches</h3>
            <p class="text-sm text-slate-400">Use this section as your historical reference for recent match outcomes.</p>
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
                        @forelse ($completedMatches as $game)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $homeName = $game->homeTeam?->name ?? 'TBD Team';
                                            $homeLogo = $teamLogoUrl($game->homeTeam?->logo_path);
                                        @endphp
                                        @if ($homeLogo !== null)
                                            <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                                        @endif
                                        <span>{{ $homeName }}</span>
                                    </div>
                                    vs
                                    <div class="mt-1 flex items-center gap-2">
                                        @php
                                            $awayName = $game->awayTeam?->name ?? 'TBD Team';
                                            $awayLogo = $teamLogoUrl($game->awayTeam?->logo_path);
                                        @endphp
                                        @if ($awayLogo !== null)
                                            <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                                        @endif
                                        <span>{{ $awayName }}</span>
                                    </div>
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
