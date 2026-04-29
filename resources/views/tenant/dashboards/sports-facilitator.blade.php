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

        $hasGamesTable = \Illuminate\Support\Facades\Schema::hasTable('games');
        $hasAuditsTable = \Illuminate\Support\Facades\Schema::hasTable('game_result_audits');
        $today = now()->toDateString();
        $userId = auth()->id();

        $gamesScheduledToday = collect();
        $pendingResultReports = 0;
        $awaitingAuditConfirmation = 0;
        $submittedReports = collect();

        if ($hasGamesTable) {
            $gamesScheduledToday = \App\Models\Game::query()
                ->with(['sport', 'homeTeam', 'awayTeam', 'venue'])
                ->whereDate('scheduled_at', $today)
                ->orderBy('scheduled_at')
                ->get();

            $pendingResultReports = \App\Models\Game::query()
                ->where('status', 'scheduled')
                ->where('scheduled_at', '<', now())
                ->count();
        }

        if ($hasAuditsTable) {
            $awaitingAuditConfirmation = \App\Models\GameResultAudit::query()
                ->where('changed_by_user_id', $userId)
                ->where('new_status', 'completed')
                ->count();

            $submittedReports = \App\Models\GameResultAudit::query()
                ->with(['game.sport', 'game.homeTeam', 'game.awayTeam'])
                ->where('changed_by_user_id', $userId)
                ->latest()
                ->limit(8)
                ->get();
        }
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">Sports Facilitator Dashboard</h2>
            <p class="mt-1 text-sm text-slate-300">Stream-style match flow for reporting and audit tracking.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-3xl border border-cyan-500/20 bg-slate-900/85 p-8 shadow-2xl">
            <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan-500/5 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-indigo-500/5 blur-3xl"></div>
            
            <div class="relative flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-cyan-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-400">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                        </span>
                        Activity Stream
                    </span>
                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-4xl">Match Facilitator Hub</h1>
                    <p class="mt-2 max-w-2xl text-base text-slate-400">Real-time match reporting and outcome verification center.</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('tenant.games.index') }}" class="group flex items-center gap-2 rounded-2xl bg-cyan-500 px-6 py-3 font-bold text-slate-900 transition-all hover:bg-cyan-400 hover:shadow-lg hover:shadow-cyan-500/25">
                        <span>Report Result</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:translate-x-1"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-cyan-500/30 hover:shadow-xl">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-500/10 blur-2xl group-hover:bg-cyan-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Scheduled Today</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($gamesScheduledToday->count()) }}</p>
                    </div>
                    <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-500/10 blur-2xl group-hover:bg-amber-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Pending Reports</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($pendingResultReports) }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all duration-300 hover:border-emerald-500/30 hover:shadow-xl">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl group-hover:bg-emerald-500/20"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Audit Confirmations</p>
                        <p class="mt-2 text-3xl font-bold text-white">{{ number_format($awaitingAuditConfirmation) }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><path d="m21 16-4-4 4-4"/><path d="M17 12H3"/><path d="m3 16 4-4-4-4"/></svg>
                <h3 class="text-lg font-bold text-white">Today's Assigned Matches</h3>
            </div>
            <div class="space-y-4">
                @forelse ($gamesScheduledToday as $game)
                    <article class="group relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-6 transition-all hover:border-cyan-500/30">
                        <div class="flex flex-wrap items-center justify-between gap-6">
                            <div class="flex items-center gap-8">
                                @php
                                    $homeName = $game->homeTeam?->name ?? 'TBD Team';
                                    $awayName = $game->awayTeam?->name ?? 'TBD Team';
                                    $homeLogo = $mediaUrl($game->homeTeam?->logo_path);
                                    $awayLogo = $mediaUrl($game->awayTeam?->logo_path);
                                @endphp
                                <div class="flex flex-col items-center gap-2">
                                    <div class="relative">
                                        <div class="absolute -inset-1 rounded-full bg-gradient-to-tr from-cyan-500 to-indigo-500 opacity-20 blur group-hover:opacity-40"></div>
                                        <img src="{{ $homeLogo ?? 'https://ui-avatars.com/api/?name='.urlencode($homeName).'&background=0D1117&color=fff' }}" alt="{{ $homeName }}" class="relative h-14 w-14 rounded-full border border-white/20 object-cover shadow-2xl" />
                                    </div>
                                    <span class="text-sm font-bold text-white">{{ $homeName }}</span>
                                </div>

                                <div class="flex flex-col items-center">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/5 text-[10px] font-black text-slate-500">VS</div>
                                    <div class="mt-2 h-px w-12 bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
                                </div>

                                <div class="flex flex-col items-center gap-2">
                                    <div class="relative">
                                        <div class="absolute -inset-1 rounded-full bg-gradient-to-tr from-indigo-500 to-rose-500 opacity-20 blur group-hover:opacity-40"></div>
                                        <img src="{{ $awayLogo ?? 'https://ui-avatars.com/api/?name='.urlencode($awayName).'&background=0D1117&color=fff' }}" alt="{{ $awayName }}" class="relative h-14 w-14 rounded-full border border-white/20 object-cover shadow-2xl" />
                                    </div>
                                    <span class="text-sm font-bold text-white">{{ $awayName }}</span>
                                </div>
                            </div>

                            <div class="flex-1 border-l border-white/5 pl-6">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-white/5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-cyan-400">{{ $game->sport?->name ?? 'Sport' }}</span>
                                    <span class="text-[10px] text-slate-500">•</span>
                                    <span class="text-[10px] font-medium text-slate-400">{{ $game->venue?->name ?? 'No venue' }}</span>
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        {{ $game->scheduled_at?->format('h:i A') }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                        {{ $game->scheduled_at?->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a href="{{ route('tenant.games.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-4 py-2.5 text-xs font-bold text-cyan-400 transition-all hover:bg-cyan-500 hover:text-slate-900">
                                    Report Result
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/10 bg-slate-900/50 p-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-slate-600 mb-4"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
                        <p class="text-sm text-slate-400">No assigned matches for today.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <h3 class="text-lg font-semibold text-slate-100">Recently Submitted Reports</h3>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Match</th>
                            <th class="px-4 py-3 text-left font-medium">Sport</th>
                            <th class="px-4 py-3 text-left font-medium">Submitted Status</th>
                            <th class="px-4 py-3 text-left font-medium">Submitted At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($submittedReports as $report)
                            <tr>
                                <td class="px-4 py-3">
                                    @php
                                        $reportHome = $report->game?->homeTeam?->name ?? 'TBD Team';
                                        $reportAway = $report->game?->awayTeam?->name ?? 'TBD Team';
                                        $reportHomeLogo = $mediaUrl($report->game?->homeTeam?->logo_path);
                                        $reportAwayLogo = $mediaUrl($report->game?->awayTeam?->logo_path);
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        @if ($reportHomeLogo !== null)
                                            <img src="{{ $reportHomeLogo }}" alt="{{ $reportHome }}" class="h-8 w-8 rounded-full border border-white/15 object-cover" />
                                        @endif
                                        <span>{{ $reportHome }}</span>
                                        <span class="text-slate-500">vs</span>
                                        @if ($reportAwayLogo !== null)
                                            <img src="{{ $reportAwayLogo }}" alt="{{ $reportAway }}" class="h-8 w-8 rounded-full border border-white/15 object-cover" />
                                        @endif
                                        <span>{{ $reportAway }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $report->game?->sport?->name ?? 'Sport' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-emerald-300/35 bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-100">
                                        {{ strtoupper($report->new_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $report->created_at?->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-400">
                                    No submitted reports yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-sm text-slate-300">
            Use <a href="{{ route('tenant.games.index') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Schedules</a> to update game outcomes and
            <a href="{{ route('tenant.audits.game-results.index') }}" class="font-semibold text-cyan-200 hover:text-cyan-100">Result Audits</a> to verify changes.
        </div>
    </div>
</x-app-layout>
