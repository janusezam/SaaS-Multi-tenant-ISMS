<x-app-layout>
    @php
        $hasTeamsTable = \Illuminate\Support\Facades\Schema::hasTable('teams');
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');
        $hasParticipationTables = \Illuminate\Support\Facades\Schema::hasTable('game_team_participations')
            && \Illuminate\Support\Facades\Schema::hasTable('game_player_assignments');
        $hasAnnouncementsTable = \Illuminate\Support\Facades\Schema::hasTable('team_announcements');

        $coachUser = auth()->user();
        $myTeam = null;
        $roster = collect();
        $upcomingMatches = collect();
        $assignmentsByGame = collect();
        $participationsByGame = collect();
        $announcements = collect();
        $recentResults = collect();
        $wins = 0;
        $losses = 0;
        $draws = 0;
        $pointsFor = 0;
        $pointsAgainst = 0;

        if ($hasTeamsTable && $coachUser !== null) {
            $myTeam = \App\Models\Team::query()
                ->with('sport')
                ->where('coach_email', $coachUser->email)
                ->first();
        }

        if ($hasPlayersTable && $myTeam !== null) {
            $roster = \App\Models\Player::query()
                ->where('team_id', $myTeam->id)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        if ($hasGamesTable && $myTeam !== null) {
            $upcomingMatches = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();

            $recentResults = \App\Models\Game::query()
                ->with(['homeTeam', 'awayTeam', 'venue'])
                ->where(function ($query) use ($myTeam): void {
                    $query->where('home_team_id', $myTeam->id)
                        ->orWhere('away_team_id', $myTeam->id);
                })
                ->where('status', 'completed')
                ->latest('scheduled_at')
                ->limit(6)
                ->get();

            foreach ($recentResults as $game) {
                $isHome = (int) $game->home_team_id === (int) $myTeam->id;
                $myScore = $isHome ? (int) ($game->home_score ?? 0) : (int) ($game->away_score ?? 0);
                $opponentScore = $isHome ? (int) ($game->away_score ?? 0) : (int) ($game->home_score ?? 0);

                $pointsFor += $myScore;
                $pointsAgainst += $opponentScore;

                if ($myScore > $opponentScore) {
                    $wins++;
                } elseif ($myScore < $opponentScore) {
                    $losses++;
                } else {
                    $draws++;
                }
            }
        }

        if ($hasParticipationTables && $myTeam !== null && $upcomingMatches->isNotEmpty()) {
            $gameIds = $upcomingMatches->pluck('id');

            $assignmentsByGame = \App\Models\GamePlayerAssignment::query()
                ->where('team_id', $myTeam->id)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->groupBy('game_id');

            $participationsByGame = \App\Models\GameTeamParticipation::query()
                ->where('team_id', $myTeam->id)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');
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
        <h2 class="text-2xl font-semibold text-slate-100">My Team</h2>
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
            <p class="text-sm text-cyan-200">Manage your lineup, confirm participation, and communicate with your team.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
                @if ($myTeam?->division)
                    · {{ $myTeam->division }} Division
                @endif
            </p>
            <p class="mt-2 text-xs text-slate-400">Workflow: 1) Set lineup per match 2) Confirm participation 3) Post announcements.</p>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="My team sections">
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
                    @click="activeTab = 'lineup'"
                    :aria-selected="activeTab === 'lineup'"
                    :class="activeTab === 'lineup' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Lineup
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'roster'"
                    :aria-selected="activeTab === 'roster'"
                    :class="activeTab === 'roster' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Roster
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'announcements'"
                    :aria-selected="activeTab === 'announcements'"
                    :class="activeTab === 'announcements' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-white/10 hover:text-white'"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    Announcements
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
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Active Roster</p>
                    <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($roster->count()) }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Upcoming Matches</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-200">{{ number_format($upcomingMatches->count()) }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Team Record</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-200">{{ $wins }} - {{ $losses }} - {{ $draws }}</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Points (For / Against)</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-100">{{ $pointsFor }} / {{ $pointsAgainst }}</p>
                </article>
            </div>
        </section>

        <section x-show="activeTab === 'lineup'" class="space-y-4" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Manage Team Lineup And Match Participation</h3>
            <p class="text-sm text-slate-400">Select players for each upcoming match, mark starters, and confirm team participation.</p>
            @if (! $hasParticipationTables)
                <div class="rounded-2xl border border-amber-300/40 bg-amber-500/20 p-4 text-sm text-amber-100">
                    Lineup actions are unavailable until engagement tables are migrated for this tenant.
                </div>
            @endif
            @forelse ($upcomingMatches as $game)
                @php
                    $existingAssignments = $assignmentsByGame->get($game->id, collect());
                    $selectedIds = $existingAssignments->pluck('player_id')->map(fn (mixed $id): int => (int) $id)->all();
                    $starterIds = $existingAssignments->where('is_starter', true)->pluck('player_id')->map(fn (mixed $id): int => (int) $id)->all();
                    $participation = $participationsByGame->get($game->id);
                    $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                    $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                @endphp
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-100">vs {{ $opponent ?? 'TBD Team' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y h:i A') }} · {{ $game->venue?->name ?? 'No venue assigned' }}</p>
                        </div>
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $participation?->coach_confirmed_at ? 'border-emerald-300/35 bg-emerald-500/20 text-emerald-100' : 'border-amber-300/35 bg-amber-500/20 text-amber-100' }}">
                            {{ $participation?->coach_confirmed_at ? 'TEAM CONFIRMED' : 'AWAITING CONFIRMATION' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('tenant.coach.games.lineup.update', $game) }}" class="mt-4 space-y-4">
                        @csrf

                        <p class="text-xs text-slate-400">Tip: players checked in the left box are assigned; "Starter" sets your starting lineup.</p>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @forelse ($roster as $player)
                                <label class="rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-medium text-slate-100">{{ $player->last_name }}, {{ $player->first_name }}</span>
                                        <input type="checkbox" name="player_ids[]" value="{{ $player->id }}" class="rounded border-slate-500 bg-slate-800 text-cyan-400" @checked(in_array((int) $player->id, $selectedIds, true))>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-slate-300">
                                        <span>{{ $player->position ?: 'No position' }}</span>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" name="starter_player_ids[]" value="{{ $player->id }}" class="rounded border-slate-500 bg-slate-800 text-emerald-400" @checked(in_array((int) $player->id, $starterIds, true))>
                                            Starter
                                        </label>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-slate-400">No players found for lineup assignment.</p>
                            @endforelse
                        </div>

                        <div>
                            <label for="coach_note_{{ $game->id }}" class="text-xs uppercase tracking-[0.12em] text-slate-400">Coach Note</label>
                            <input id="coach_note_{{ $game->id }}" name="coach_note" type="text" maxlength="255" value="{{ $participation?->coach_note }}" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300/45 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="confirm_team" value="1" class="rounded border-slate-500 bg-slate-800 text-emerald-400" @checked($participation?->coach_confirmed_at !== null)>
                            Confirm Team Participation For This Match
                        </label>

                        <div>
                            <button type="submit" class="rounded-xl border border-cyan-300/35 bg-cyan-500/20 px-4 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-500/30">
                                Save Lineup
                            </button>
                        </div>
                    </form>
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                    No upcoming matches available for lineup actions.
                </div>
            @endforelse
        </section>

        <section x-show="activeTab === 'roster'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Team Roster</h3>
            <p class="text-sm text-slate-400">Reference list of all players currently tied to your team profile.</p>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Player</th>
                            <th class="px-4 py-3 text-left font-medium">Student ID</th>
                            <th class="px-4 py-3 text-left font-medium">Position</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($roster as $player)
                            <tr>
                                <td class="px-4 py-3">{{ $player->last_name }}, {{ $player->first_name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $player->student_id }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $player->position ?? 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $player->is_active ? 'border-emerald-300/35 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/35 bg-slate-500/20 text-slate-200' }}">
                                        {{ $player->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">No players found for your team.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="activeTab === 'announcements'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Post Team Announcement</h3>
            <p class="text-sm text-slate-400">Share reminders, updates, and instructions with your players.</p>
            @if ($hasAnnouncementsTable)
                <form method="POST" action="{{ route('tenant.coach.announcements.store') }}" class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="text-xs uppercase tracking-[0.12em] text-slate-400">Title</label>
                        <input id="title" name="title" type="text" maxlength="120" required class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300/45 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                    </div>
                    <div>
                        <label for="body" class="text-xs uppercase tracking-[0.12em] text-slate-400">Message</label>
                        <textarea id="body" name="body" rows="3" maxlength="2000" required class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-300/45 focus:outline-none focus:ring-2 focus:ring-cyan-400/30"></textarea>
                    </div>
                    <button type="submit" class="rounded-xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-2 text-sm font-semibold text-emerald-100 hover:bg-emerald-500/30">
                        Publish Announcement
                    </button>
                </form>
            @else
                <div class="rounded-2xl border border-amber-300/40 bg-amber-500/20 p-4 text-sm text-amber-100">
                    Announcements are unavailable until engagement tables are migrated for this tenant.
                </div>
            @endif

            <h3 class="text-lg font-semibold text-slate-100">Recent Team Announcements</h3>
            <p class="text-sm text-slate-400">Latest messages are shown first so players can catch up quickly.</p>
            <div class="space-y-3">
                @forelse ($announcements as $announcement)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">{{ $announcement->title }}</p>
                        <p class="mt-2 text-sm text-slate-300">{{ $announcement->body }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $announcement->published_at?->format('M d, Y h:i A') ?? 'Draft' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No announcements posted yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'results'" class="space-y-3" role="tabpanel" x-cloak>
            <h3 class="text-lg font-semibold text-slate-100">Recent Match Results</h3>
            <p class="text-sm text-slate-400">Quick reference for team performance trends from recent completed matches.</p>
            <div class="space-y-3">
                @forelse ($recentResults as $game)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                        <p class="text-sm font-semibold text-slate-100">
                            {{ $game->homeTeam?->name ?? 'TBD Team' }}
                            <span class="px-1 text-slate-400">vs</span>
                            {{ $game->awayTeam?->name ?? 'TBD Team' }}
                        </p>
                        <p class="mt-2 text-sm text-cyan-200">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y') }} · {{ $game->venue?->name ?? 'No venue assigned' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No completed matches found.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
