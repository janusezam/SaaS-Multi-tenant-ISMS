<x-app-layout>
    @php
        // No heavy logic needed here anymore as it's passed from the controller.
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Team</h2>
    </x-slot>

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

        $profilePhotoUrl = static function (?string $path): ?string {
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
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Team Workspace</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-100 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-300/35 bg-rose-500/20 px-4 py-3 text-sm text-rose-100 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200 shadow-xl backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-[0.03]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="h-20 w-20 rounded-2xl border-2 border-cyan-500/30 bg-slate-800 overflow-hidden shadow-lg p-1">
                        <div class="h-full w-full rounded-xl overflow-hidden bg-slate-900">
                            @php $tLogo = $teamLogoUrl($myTeam->logo_path); @endphp
                            @if ($tLogo)
                                <img src="{{ $tLogo }}" alt="{{ $myTeam->name }}" class="h-full w-full object-cover" />
                            @else
                                <div class="flex h-full w-full items-center justify-center text-2xl font-black text-slate-700">
                                    {{ strtoupper(substr($myTeam->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-100 tracking-tight">{{ $myTeam->name }}</h2>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-cyan-400 uppercase tracking-widest">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ $myTeam->sport?->name ?? 'General Sport' }}
                            </span>
                            <span class="h-1 w-1 rounded-full bg-slate-700"></span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 uppercase tracking-widest">
                                {{ $myTeam->division ?? 'No Division' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Team Coach</p>
                        <p class="text-sm font-bold text-slate-200">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-full border border-white/10 bg-slate-800 overflow-hidden">
                        @php $pPhoto = $profilePhotoUrl(auth()->user()->profile_photo_path); @endphp
                        @if ($pPhoto)
                            <img src="{{ $pPhoto }}" class="h-full w-full object-cover" />
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-cyan-500/20 text-xs font-bold text-cyan-400">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2 shadow-lg">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="My team sections">
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'overview'"
                    :aria-selected="activeTab === 'overview'"
                    :class="activeTab === 'overview' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Overview
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'lineup'"
                    :aria-selected="activeTab === 'lineup'"
                    :class="activeTab === 'lineup' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Lineup
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'roster'"
                    :aria-selected="activeTab === 'roster'"
                    :class="activeTab === 'roster' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Roster
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'announcements'"
                    :aria-selected="activeTab === 'announcements'"
                    :class="activeTab === 'announcements' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    Announcements
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'results'"
                    :aria-selected="activeTab === 'results'"
                    :class="activeTab === 'results' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Results
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-4" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Active Roster</p>
                            <p class="mt-2 text-3xl font-bold text-cyan-200">{{ number_format($roster->count()) }}</p>
                        </div>
                        <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Upcoming Matches</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-200">{{ number_format($upcomingMatches->count()) }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Team Record</p>
                            <p class="mt-2 text-2xl font-bold text-amber-200">{{ $wins }}W - {{ $losses }}L - {{ $draws }}D</p>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Avg Points</p>
                            <p class="mt-2 text-2xl font-bold text-slate-100">{{ $pointsFor }} / {{ $pointsAgainst }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-500/10 p-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section x-show="activeTab === 'lineup'" class="space-y-4" role="tabpanel" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Manage Team Lineup And Match Participation</h3>
                    <p class="text-sm text-slate-400">Select players for each upcoming match, mark starters, and confirm team participation.</p>
                </div>
            </div>
            @if (! $canManageLineup)
                <div class="rounded-2xl border border-amber-300/40 bg-amber-500/20 p-4 text-sm text-amber-100 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M13.477 3.03a.75.75 0 01.456.696v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.456-.696zM10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM6.523 3.03a.75.75 0 01.456.696v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.456-.696zM10 5.5a4.5 4.5 0 100 9 4.5 4.5 0 000-9zM8 10a2 2 0 114 0 2 2 0 01-4 0z" clip-rule="evenodd" />
                    </svg>
                    RBAC: Lineup management is currently disabled for Team Coach.
                </div>
            @endif
            @if (! $hasParticipationTables)
                <div class="rounded-2xl border border-rose-300/40 bg-rose-500/20 p-4 text-sm text-rose-100 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    Engagement tables are not migrated. Contact Support.
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
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 shadow-xl backdrop-blur-md transition hover:border-cyan-500/20">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/5 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="rounded-xl bg-slate-800 p-3 text-cyan-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-base font-bold text-slate-100">vs {{ $opponent ?? 'TBD Team' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400 flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                                    <span class="mx-1 text-white/5">|</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ $game->venue?->name ?? 'No venue assigned' }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-bold tracking-widest uppercase {{ $participation?->coach_confirmed_at ? 'border-emerald-300/35 bg-emerald-500/10 text-emerald-300' : 'border-amber-300/35 bg-amber-500/10 text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $participation?->coach_confirmed_at ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]' : 'bg-amber-500' }}"></span>
                            {{ $participation?->coach_confirmed_at ? 'TEAM CONFIRMED' : 'AWAITING CONFIRMATION' }}
                        </span>
                    </div>

                    @if ($canManageLineup)
                        <form method="POST" action="{{ route('tenant.coach.games.lineup.update', $game) }}" class="mt-6 space-y-6">
                            @csrf
                            
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @forelse ($roster as $player)
                                    <label class="group relative rounded-xl border border-white/5 bg-white/[0.03] p-4 transition hover:bg-white/[0.06] hover:border-cyan-500/30">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-lg border border-white/10 bg-slate-800 overflow-hidden">
                                                    @php $plPhoto = $profilePhotoUrl($player->user?->profile_photo_path); @endphp
                                                    @if ($plPhoto)
                                                        <img src="{{ $plPhoto }}" class="h-full w-full object-cover" />
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center bg-slate-700 text-[10px] font-bold text-slate-400">
                                                            {{ strtoupper(substr($player->first_name, 0, 1)) }}{{ strtoupper(substr($player->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-100">{{ $player->last_name }}, {{ $player->first_name }}</span>
                                                    <span class="text-[10px] uppercase tracking-wider text-slate-500">{{ $player->position ?: 'No position' }}</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="player_ids[]" value="{{ $player->id }}" class="h-5 w-5 rounded border-white/20 bg-slate-800 text-cyan-500 focus:ring-cyan-400 focus:ring-offset-slate-900" @checked(in_array((int) $player->id, $selectedIds, true))>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-white/5 pt-3">
                                            <span class="text-[10px] text-slate-400 font-medium">Starter Status</span>
                                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="starter_player_ids[]" value="{{ $player->id }}" class="h-4 w-4 rounded-full border-white/20 bg-slate-800 text-emerald-500 focus:ring-emerald-400 focus:ring-offset-slate-900" @checked(in_array((int) $player->id, $starterIds, true))>
                                                <span class="text-[10px] font-bold text-slate-400 group-hover:text-emerald-300 transition">STARTER</span>
                                            </label>
                                        </div>
                                    </label>
                                @empty
                                    <div class="lg:col-span-3 py-6 text-center text-sm text-slate-500">No players found in your roster.</div>
                                @endforelse
                            </div>

                            <div class="space-y-4 rounded-xl border border-white/5 bg-slate-950/40 p-4">
                                <div>
                                    <label for="coach_note_{{ $game->id }}" class="text-[10px] uppercase tracking-[0.15em] text-slate-400 font-bold mb-1.5 block">Strategy Note</label>
                                    <textarea id="coach_note_{{ $game->id }}" name="coach_note" rows="2" maxlength="255" class="w-full rounded-xl border border-white/10 bg-slate-900/60 px-4 py-2 text-sm text-slate-100 placeholder-slate-600 focus:border-cyan-400/50 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 transition" placeholder="Add a note for the team or facilitator...">{{ $participation?->coach_note }}</textarea>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <label class="inline-flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" name="confirm_team" value="1" class="h-5 w-5 rounded border-white/20 bg-slate-800 text-emerald-500 focus:ring-emerald-400 focus:ring-offset-slate-900" @checked($participation?->coach_confirmed_at !== null)>
                                        <span class="text-sm font-semibold text-slate-300 group-hover:text-emerald-300 transition">Confirm Team Readiness For Match</span>
                                    </label>
                                    
                                    <button type="submit" class="flex items-center gap-2 rounded-xl bg-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-900/20 transition hover:bg-cyan-500 hover:scale-[1.02] active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="mt-6 flex items-center gap-3 rounded-xl bg-amber-500/10 p-4 border border-amber-500/20 text-sm text-amber-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                            You can view this match context, but lineup editing is disabled by tenant RBAC.
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-12 text-center shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h4 class="mt-4 text-lg font-bold text-slate-300">No Match Lineups Pending</h4>
                    <p class="mt-2 text-sm text-slate-500">There are no upcoming matches requiring lineup assignments at this time.</p>
                </div>
            @endforelse
        </section>

        <section x-show="activeTab === 'roster'" class="space-y-4" role="tabpanel" x-cloak>
            <div>
                <h3 class="text-lg font-semibold text-slate-100">Team Roster</h3>
                <p class="text-sm text-slate-400">Reference list of all players currently tied to your team profile.</p>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85 shadow-xl backdrop-blur-md">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Player Name</th>
                            <th class="px-6 py-4 text-left font-semibold">Student ID</th>
                            <th class="px-6 py-4 text-left font-semibold">Position</th>
                            <th class="px-6 py-4 text-center font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($roster as $player)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full border border-white/10 bg-slate-800 overflow-hidden">
                                            @php $plPhotoTable = $profilePhotoUrl($player->user?->profile_photo_path); @endphp
                                            @if ($plPhotoTable)
                                                <img src="{{ $plPhotoTable }}" class="h-full w-full object-cover" />
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-slate-700 text-[10px] font-bold text-slate-500">
                                                    {{ strtoupper(substr($player->first_name, 0, 1)) }}{{ strtoupper(substr($player->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="font-semibold text-slate-100">{{ $player->last_name }}, {{ $player->first_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-400 font-mono text-xs">{{ $player->student_id }}</td>
                                <td class="px-6 py-4 text-slate-300">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-1 w-1 rounded-full bg-cyan-400"></span>
                                        {{ $player->position ?? 'Unassigned' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-widest uppercase {{ $player->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                                        {{ $player->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">No players found for your team roster.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="activeTab === 'announcements'" class="space-y-6" role="tabpanel" x-cloak>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1 space-y-4">
                    <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        Post Announcement
                    </h3>
                    <p class="text-sm text-slate-400">Share reminders, updates, and instructions with your players instantly.</p>
                    
                    @if (! $canManageAnnouncements)
                        <div class="rounded-xl border border-amber-300/40 bg-amber-500/20 p-4 text-xs text-amber-100">
                            Announcement publishing is disabled for your role.
                        </div>
                    @elseif ($hasAnnouncementsTable)
                        <form method="POST" action="{{ route('tenant.coach.announcements.store') }}" class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 space-y-4 shadow-xl backdrop-blur-md">
                            @csrf
                            <div>
                                <label for="title" class="text-[10px] uppercase tracking-[0.12em] text-slate-400 font-bold mb-1 block">Title</label>
                                <input id="title" name="title" type="text" maxlength="120" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-sm text-slate-100 focus:border-emerald-400/50 focus:outline-none focus:ring-2 focus:ring-emerald-400/20 transition" placeholder="e.g. Mandatory Practice Tomorrow">
                            </div>
                            <div>
                                <label for="body" class="text-[10px] uppercase tracking-[0.12em] text-slate-400 font-bold mb-1 block">Message Content</label>
                                <textarea id="body" name="body" rows="4" maxlength="2000" required class="w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-2 text-sm text-slate-100 focus:border-emerald-400/50 focus:outline-none focus:ring-2 focus:ring-emerald-400/20 transition" placeholder="Type your message here..."></textarea>
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500 hover:scale-[1.01] active:scale-[0.98]">
                                Publish to Team
                            </button>
                        </form>
                    @else
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-xs text-rose-200">
                            Engagement tables are missing. Contact Admin.
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Recent Team Announcements
                    </h3>
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse ($announcements as $announcement)
                            <article class="relative overflow-hidden rounded-2xl border border-white/5 bg-white/[0.03] p-5 shadow-lg transition hover:bg-white/[0.05]">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="text-base font-bold text-slate-100">{{ $announcement->title }}</p>
                                        <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ $announcement->body }}</p>
                                        <p class="mt-4 text-[10px] uppercase tracking-widest text-slate-500 font-bold flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $announcement->published_at?->format('M d, Y · h:i A') ?? 'Draft Status' }}
                                        </p>
                                    </div>
                                    <div class="rounded-full bg-cyan-500/10 p-2 text-cyan-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-white/5 bg-slate-900/40 p-12 text-center">
                                <p class="text-sm text-slate-500">No announcements posted to your team yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section x-show="activeTab === 'results'" class="space-y-4" role="tabpanel" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Recent Match Results
                    </h3>
                    <p class="text-sm text-slate-400">Quick reference for team performance trends from recent completed matches.</p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($recentResults as $game)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-xl backdrop-blur-md">
                        <div class="flex items-center justify-between mb-4 border-b border-white/5 pb-3">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $game->scheduled_at?->format('M d, Y') }}</span>
                            <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-400 uppercase tracking-widest">FINAL</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-100 truncate pr-2">{{ $game->homeTeam?->name ?? 'TBD Team' }}</span>
                                <span class="text-xl font-black text-cyan-400">{{ $game->home_score ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-100 truncate pr-2">{{ $game->awayTeam?->name ?? 'TBD Team' }}</span>
                                <span class="text-xl font-black text-slate-400">{{ $game->away_score ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/5">
                            <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ $game->venue?->name ?? 'No venue assigned' }}
                            </p>
                        </div>
                    </article>
                @empty
                    <div class="lg:col-span-3 rounded-2xl border border-white/5 bg-slate-900/40 p-12 text-center text-slate-500">
                        No completed match results found for your team.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

</x-app-layout>
