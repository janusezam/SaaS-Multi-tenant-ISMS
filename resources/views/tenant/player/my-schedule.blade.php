<x-app-layout>
    @php
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');
        $hasAssignmentsTable = \Illuminate\Support\Facades\Schema::hasTable('game_player_assignments');
        $hasAnnouncementsTable = \Illuminate\Support\Facades\Schema::hasTable('team_announcements');

        $playerUser = auth()->user();
        $playerProfile = null;
        $myTeam = null;
        $teamRoster = collect();
        $upcomingMatches = collect();
        $recentResults = collect();
        $myCompletedMatches = collect();
        $assignmentsByGame = collect();
        $announcements = collect();
        $nextMatchDate = null;
        $standingRank = null;
        $lastMatchResult = 'N/A';
        $acceptedCount = 0;
        $declinedCount = 0;
        $pendingCount = 0;
        $starterCount = 0;

        if ($hasPlayersTable && $playerUser !== null) {
            $playerProfile = \App\Models\Player::query()
                ->with(['team.sport'])
                ->where('email', $playerUser->email)
                ->first();

            $myTeam = $playerProfile?->team;

            if ($myTeam !== null) {
                $teamRoster = \App\Models\Player::query()
                    ->where('team_id', $myTeam->id)
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
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

        if ($hasAssignmentsTable && $playerProfile !== null && $myTeam !== null) {
            $myAssignments = \App\Models\GamePlayerAssignment::query()
                ->with(['game.homeTeam', 'game.awayTeam', 'assignedBy'])
                ->where('player_id', $playerProfile->id)
                ->where('team_id', $myTeam->id)
                ->get();

            $assignmentsByGame = $myAssignments->keyBy('game_id');
            $acceptedCount = $myAssignments->where('attendance_status', 'accepted')->count();
            $declinedCount = $myAssignments->where('attendance_status', 'declined')->count();
            $pendingCount = $myAssignments->where('attendance_status', 'pending')->count();
            $starterCount = $myAssignments->where('is_starter', true)->count();

            $myCompletedMatches = $myAssignments
                ->filter(fn ($assignment) => $assignment->game?->status === 'completed')
                ->sortByDesc(fn ($assignment) => $assignment->game?->scheduled_at)
                ->take(10);
        }

        if ($hasAnnouncementsTable && $myTeam !== null) {
            $announcements = \App\Models\TeamAnnouncement::query()
                ->where('team_id', $myTeam->id)
                ->latest('published_at')
                ->latest('id')
                ->limit(8)
                ->get();
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Schedule</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/35 bg-rose-500/20 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Confirm your attendance, track your stats, and stay updated with team announcements.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your player account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
            </p>
            <p class="mt-2 text-xs text-slate-400">Workflow: check assignments, respond Accept/Decline, then review announcements and history.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="Player schedule sections">
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
                    @click="activeTab = 'attendance'"
                    :aria-selected="activeTab === 'attendance'"
                    :class="activeTab === 'attendance' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Attendance
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'team'"
                    :aria-selected="activeTab === 'team'"
                    :class="activeTab === 'team' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Team
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'history'"
                    :aria-selected="activeTab === 'history'"
                    :class="activeTab === 'history' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    History
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-4" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Accepted</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ $acceptedCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Declined</p>
                    <p class="mt-2 text-3xl font-semibold text-rose-200">{{ $declinedCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Pending</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-200">{{ $pendingCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Starter Selections</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ $starterCount }}</p>
                </article>
            </div>
        </section>

        <section x-show="activeTab === 'attendance'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Upcoming Matches And Attendance</h3>
            <p class="text-sm text-slate-400">Respond to each assigned match so your coach sees your availability in real time.</p>
            <div class="space-y-3">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                        $assignment = $assignmentsByGame->get($game->id);
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">vs {{ $opponent ?? 'TBD Team' }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                            · {{ $game->venue?->name ?? 'No venue assigned' }}
                            · {{ $game->sport?->name ?? 'Sport' }}
                        </p>

                        @if ($assignment !== null)
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $assignment->attendance_status === 'accepted' ? 'border-emerald-300/35 bg-emerald-500/20 text-emerald-100' : ($assignment->attendance_status === 'declined' ? 'border-rose-300/35 bg-rose-500/20 text-rose-100' : 'border-amber-300/35 bg-amber-500/20 text-amber-100') }}">
                                    {{ strtoupper($assignment->attendance_status) }}
                                </span>
                                @if ($assignment->is_starter)
                                    <span class="inline-flex rounded-full border border-cyan-300/35 bg-cyan-500/20 px-2.5 py-1 text-xs font-semibold text-cyan-100">STARTER</span>
                                @endif
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Assigned by: {{ $assignment->assignedBy?->name ?? 'Team Coach' }}
                            </p>

                            <div class="mt-3 flex gap-2">
                                <form method="POST" action="{{ route('tenant.player.assignments.attendance.update', $assignment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="attendance_status" value="accepted">
                                    <button type="submit" class="rounded-lg border border-emerald-300/35 bg-emerald-500/20 px-3 py-1.5 text-xs font-semibold text-emerald-100 hover:bg-emerald-500/30">Accept</button>
                                </form>

                                <form method="POST" action="{{ route('tenant.player.assignments.attendance.update', $assignment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="attendance_status" value="declined">
                                    <button type="submit" class="rounded-lg border border-rose-300/35 bg-rose-500/20 px-3 py-1.5 text-xs font-semibold text-rose-100 hover:bg-rose-500/30">Decline</button>
                                </form>
                            </div>
                        @else
                            <p class="mt-3 text-xs text-slate-400">Coach has not assigned your lineup slot for this match yet.</p>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No upcoming matches found.
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'team'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Team Roster</h3>
            <p class="text-sm text-slate-400">View your current teammates and active/inactive status.</p>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Player</th>
                            <th class="px-4 py-3 text-left font-medium">Position</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($teamRoster as $teamPlayer)
                            <tr>
                                <td class="px-4 py-3">{{ $teamPlayer->last_name }}, {{ $teamPlayer->first_name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $teamPlayer->position ?: 'N/A' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $teamPlayer->is_active ? 'ACTIVE' : 'INACTIVE' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">No roster data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="text-lg font-semibold text-slate-100">Team Announcements</h3>
            <p class="text-sm text-slate-400">Important team messages from your coach appear here, newest first.</p>
            <div class="space-y-3">
                @forelse ($announcements as $announcement)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">{{ $announcement->title }}</p>
                        <p class="mt-2 text-sm text-slate-300">{{ $announcement->body }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $announcement->published_at?->format('M d, Y h:i A') ?? 'Draft' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No announcements yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'history'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Recent Team Results</h3>
            <p class="text-sm text-slate-400">Snapshot of your team’s latest completed games.</p>
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

            <h3 class="text-lg font-semibold text-slate-100">My Match History</h3>
            <p class="text-sm text-slate-400">Your completed assignments and attendance outcomes.</p>
            <div class="space-y-3">
                @forelse ($myCompletedMatches as $assignment)
                    @php
                        $game = $assignment->game;
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">
                            {{ $game?->homeTeam?->name ?? 'TBD Team' }}
                            <span class="px-1 text-slate-400">vs</span>
                            {{ $game?->awayTeam?->name ?? 'TBD Team' }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400">{{ $game?->scheduled_at?->format('M d, Y') }} · Attendance: {{ strtoupper($assignment->attendance_status) }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No personal match history yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
