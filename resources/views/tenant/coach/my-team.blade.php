<x-app-layout>
    @php
        $hasTeamsTable = \Illuminate\Support\Facades\Schema::hasTable('teams');
        $hasPlayersTable = \Illuminate\Support\Facades\Schema::hasTable('players');
        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');

        $coachUser = auth()->user();
        $myTeam = null;
        $roster = collect();
        $recentResults = collect();

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
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Team</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Read-only overview of your team profile and roster.</p>
            <p class="mt-2 text-sm text-slate-300">
                {{ $myTeam?->name ?? 'No team linked to your account yet.' }}
                @if ($myTeam?->sport?->name)
                    · {{ $myTeam->sport->name }}
                @endif
                @if ($myTeam?->division)
                    · {{ $myTeam->division }} Division
                @endif
            </p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Active Roster</p>
                <p class="mt-2 text-3xl font-semibold text-cyan-200">{{ number_format($roster->count()) }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Sport</p>
                <p class="mt-2 text-lg font-semibold text-emerald-200">{{ $myTeam?->sport?->name ?? 'N/A' }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Division</p>
                <p class="mt-2 text-lg font-semibold text-amber-200">{{ $myTeam?->division ?? 'N/A' }}</p>
            </article>
            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Team Status</p>
                <p class="mt-2 text-lg font-semibold text-slate-100">{{ $myTeam?->is_active ? 'Active' : 'Inactive' }}</p>
            </article>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Roster (Read-Only)</h3>
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

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recent Results (Read-Only)</h3>
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
