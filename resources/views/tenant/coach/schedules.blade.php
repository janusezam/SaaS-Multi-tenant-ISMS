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
        <h2 class="text-2xl font-semibold text-slate-100">My Team Schedules</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ activeTab: 'overview' }">
        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200 shadow-xl backdrop-blur-md relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-[0.03]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="h-16 w-16 rounded-2xl border-2 border-cyan-500/30 bg-slate-800 overflow-hidden shadow-lg">
                            @php $pPhoto = $profilePhotoUrl(auth()->user()->profile_photo_path); @endphp
                            @if ($pPhoto)
                                <img src="{{ $pPhoto }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover" />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800 text-xl font-bold text-cyan-400">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="absolute -right-1 -bottom-1 h-6 w-6 rounded-lg bg-cyan-500 border-2 border-slate-900 flex items-center justify-center shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">Coach {{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-400">Operational Dashboard & Match Planning</p>
                    </div>
                </div>

                @if ($myTeam)
                    <div class="flex items-center gap-4 rounded-xl bg-white/5 p-3 pr-5 border border-white/5">
                        <div class="h-12 w-12 rounded-lg border border-white/10 bg-slate-800 overflow-hidden shadow-inner">
                            @php $tLogo = $teamLogoUrl($myTeam->logo_path); @endphp
                            @if ($tLogo)
                                <img src="{{ $tLogo }}" alt="{{ $myTeam->name }}" class="h-full w-full object-cover" />
                            @else
                                <div class="flex h-full w-full items-center justify-center text-lg font-black text-slate-500">
                                    {{ strtoupper(substr($myTeam->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-bold text-cyan-400 uppercase tracking-widest">{{ $myTeam->name }}</p>
                            <p class="text-[10px] text-slate-500 font-medium italic">{{ $myTeam->sport?->name ?? 'Multisport' }} Division</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-slate-300 border-t border-white/5 pt-4">
                <span class="flex items-center gap-2 text-xs text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manage assignments and players in <a href="{{ route('tenant.coach.my-team') }}" class="font-bold text-cyan-400 hover:text-cyan-300 transition underline underline-offset-4">My Team Workspace</a>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2 shadow-lg">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="Coach schedules sections">
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
                    @click="activeTab = 'upcoming'"
                    :aria-selected="activeTab === 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Upcoming
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'completed'"
                    :aria-selected="activeTab === 'completed'"
                    :class="activeTab === 'completed' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Completed
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-4" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Upcoming Matches</p>
                            <p class="mt-2 text-3xl font-bold text-cyan-200">{{ $upcomingMatches->count() }}</p>
                        </div>
                        <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute -bottom-2 -right-2 h-16 w-16 opacity-[0.05] text-cyan-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Completed Matches</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-200">{{ $completedMatches->count() }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute -bottom-2 -right-2 h-16 w-16 opacity-[0.05] text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </article>

                <article class="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-slate-400 font-medium">Next Match</p>
                            <p class="mt-2 text-sm font-bold text-amber-200">{{ $nextMatch?->scheduled_at?->format('M d, Y h:i A') ?? 'No upcoming match' }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="absolute -bottom-2 -right-2 h-16 w-16 opacity-[0.05] text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </article>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-4 shadow-lg backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-amber-500/20 p-2 text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.12em] text-slate-400 font-medium">Next Action Required</p>
                        <div class="mt-1">
                            <a href="{{ route('tenant.coach.my-team') }}" class="text-sm font-semibold text-amber-200 hover:text-amber-100 transition flex items-center gap-1">
                                Go To My Team Actions
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="activeTab === 'upcoming'" class="space-y-4" role="tabpanel" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Upcoming Matches</h3>
                    <p class="text-sm text-slate-400">Review schedule timing and venue before you set lineup in My Team.</p>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                    @endphp
                    <article class="group rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg transition hover:border-cyan-500/40 hover:bg-slate-900">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                @php
                                    $opponentTeam = $isHome ? $game->awayTeam : $game->homeTeam;
                                    $opponentLogo = $teamLogoUrl($opponentTeam?->logo_path);
                                @endphp
                                <div class="relative">
                                    @if ($opponentLogo !== null)
                                        <img src="{{ $opponentLogo }}" alt="{{ $opponent ?? 'TBD Team' }}" class="h-12 w-12 rounded-full border-2 border-white/15 object-cover shadow-inner" />
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-white/15 bg-slate-800 text-lg font-bold text-slate-400">
                                            {{ strtoupper(substr($opponent ?? 'T', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-slate-900 bg-cyan-500 text-white">
                                        <span class="text-[10px] font-bold">VS</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-100 group-hover:text-cyan-200">{{ $opponent ?? 'TBD Team' }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-500">{{ $isHome ? 'Playing Home' : 'Playing Away' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $game->scheduled_at?->format('M d, Y h:i A') }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $game->venue?->name ?? 'No venue assigned' }}
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                {{ $game->sport?->name ?? 'Sport' }}
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="lg:col-span-3 rounded-2xl border border-white/10 bg-slate-900/85 p-12 text-center shadow-inner">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-800 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="mt-4 text-lg font-semibold text-slate-300">No Upcoming Matches</h4>
                        <p class="mt-2 text-sm text-slate-500">There are no scheduled games for your team at the moment.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'completed'" class="space-y-4" role="tabpanel" x-cloak>
            <div>
                <h3 class="text-lg font-semibold text-slate-100">Completed Matches</h3>
                <p class="text-sm text-slate-400">Use this section as your historical reference for recent match outcomes.</p>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85 shadow-xl backdrop-blur-md">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Matchup</th>
                            <th class="px-6 py-4 text-center font-semibold">Final Score</th>
                            <th class="px-6 py-4 text-left font-semibold">Details</th>
                            <th class="px-6 py-4 text-left font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse ($completedMatches as $game)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $homeLogo = $teamLogoUrl($game->homeTeam?->logo_path);
                                                $awayLogo = $teamLogoUrl($game->awayTeam?->logo_path);
                                            @endphp
                                            <div class="h-8 w-8 rounded-full border border-white/10 bg-slate-800 overflow-hidden flex-shrink-0">
                                                @if($homeLogo) <img src="{{ $homeLogo }}" class="h-full w-full object-cover"> @endif
                                            </div>
                                            <span class="font-medium {{ (int)$game->home_team_id === (int)$myTeam?->id ? 'text-cyan-300' : '' }}">{{ $game->homeTeam?->name ?? 'TBD Team' }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full border border-white/10 bg-slate-800 overflow-hidden flex-shrink-0">
                                                @if($awayLogo) <img src="{{ $awayLogo }}" class="h-full w-full object-cover"> @endif
                                            </div>
                                            <span class="font-medium {{ (int)$game->away_team_id === (int)$myTeam?->id ? 'text-cyan-300' : '' }}">{{ $game->awayTeam?->name ?? 'TBD Team' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col rounded-lg bg-slate-950/60 px-3 py-1.5 border border-white/5 shadow-inner">
                                        <span class="text-lg font-bold text-slate-100 tracking-widest">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</span>
                                        <span class="text-[10px] uppercase text-slate-500 font-bold">Final</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 text-xs">
                                        <span class="flex items-center gap-1.5 text-slate-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            {{ $game->venue?->name ?? 'N/A' }}
                                        </span>
                                        <span class="flex items-center gap-1.5 text-slate-400 italic">
                                            {{ $game->sport?->name ?? 'Sport' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-semibold text-slate-300 bg-white/5 px-2.5 py-1 rounded-md border border-white/5">
                                        {{ $game->scheduled_at?->format('M d, Y') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <p class="text-sm text-slate-500">No completed matches found in recent records.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</x-app-layout>
