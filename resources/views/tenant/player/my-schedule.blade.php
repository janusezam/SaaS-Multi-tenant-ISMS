<x-app-layout>
    @php
        // Data is now fetched and passed from the PlayerModuleController.
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">My Schedule</h2>
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
        <h2 class="text-2xl font-semibold text-slate-100">My Schedule</h2>
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
            <div class="absolute top-0 right-0 p-4 opacity-[0.03]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                        <div class="absolute -right-1 -bottom-1 h-6 w-6 rounded-lg bg-emerald-500 border-2 border-slate-900 flex items-center justify-center shadow-md">
                            <span class="text-[10px] font-bold text-white">#{{ $playerProfile?->student_id ? substr($playerProfile->student_id, -2) : '01' }}</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-100">{{ auth()->user()->name }}</h2>
                        <p class="text-xs text-slate-400">Student Athlete Workspace</p>
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
                            <p class="text-[10px] text-slate-500 font-medium">{{ $myTeam->sport?->name ?? 'General' }} Squad</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-slate-300 border-t border-white/5 pt-4">
                <span class="flex items-center gap-2 text-xs text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manage your profile in <a href="{{ route('tenant.profile.edit') }}" class="font-bold text-cyan-400 hover:text-cyan-300 transition underline underline-offset-4">Settings</a>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-2 shadow-lg">
            <nav class="flex flex-wrap gap-2" role="tablist" aria-label="Player profile sections">
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
                    @click="activeTab = 'attendance'"
                    :aria-selected="activeTab === 'attendance'"
                    :class="activeTab === 'attendance' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    Attendance
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'team'"
                    :aria-selected="activeTab === 'team'"
                    :class="activeTab === 'team' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Team
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="activeTab = 'history'"
                    :aria-selected="activeTab === 'history'"
                    :class="activeTab === 'history' ? 'bg-cyan-500/20 text-cyan-100 border-cyan-300/40' : 'text-slate-300 border-transparent hover:bg-white/5 hover:text-white'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    History
                </button>
            </nav>
        </div>

        <section x-show="activeTab === 'overview'" class="space-y-6" role="tabpanel">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.16em] text-slate-400 font-bold">Next Match Date</p>
                            <p class="mt-2 text-lg font-bold text-cyan-200">{{ $nextMatchDate?->format('M d, Y') ?? 'TBD' }}</p>
                            <p class="text-xs text-slate-500">{{ $nextMatchDate?->format('h:i A') }}</p>
                        </div>
                        <div class="rounded-xl bg-cyan-500/10 p-3 text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.16em] text-slate-400 font-bold">Standing Rank</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-200">{{ $standingRank !== null ? '#'.$standingRank : 'N/A' }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg backdrop-blur-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.16em] text-slate-400 font-bold">Last Result</p>
                            <p class="mt-2 text-3xl font-bold text-amber-200">{{ strtoupper($lastMatchResult) }}</p>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/5 bg-slate-950/40 p-5 shadow-inner">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Confirmed</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-400/80">{{ $acceptedCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/5 bg-slate-950/40 p-5 shadow-inner">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Declined</p>
                    <p class="mt-1 text-2xl font-bold text-rose-400/80">{{ $declinedCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/5 bg-slate-950/40 p-5 shadow-inner">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-amber-400/80">{{ $pendingCount }}</p>
                </article>
                <article class="rounded-2xl border border-white/5 bg-slate-950/40 p-5 shadow-inner">
                    <p class="text-[10px] uppercase tracking-[0.16em] text-slate-500 font-bold">Starters</p>
                    <p class="mt-1 text-2xl font-bold text-cyan-400/80">{{ $starterCount }}</p>
                </article>
            </div>
        </section>

        <section x-show="activeTab === 'attendance'" class="space-y-4" role="tabpanel" x-cloak>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Match Invitations & Attendance</h3>
                    <p class="text-sm text-slate-400">Confirm your availability for upcoming matches.</p>
                </div>
            </div>
            <div class="space-y-4">
                @forelse ($upcomingMatches as $game)
                    @php
                        $isHome = (int) $game->home_team_id === (int) $myTeam?->id;
                        $opponent = $isHome ? $game->awayTeam?->name : $game->homeTeam?->name;
                        $assignment = $assignmentsByGame->get($game->id);
                    @endphp
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 shadow-xl backdrop-blur-md transition hover:border-cyan-500/20">
                        <div class="flex flex-wrap items-center justify-between gap-4">
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
                                        {{ $game->venue?->name ?? 'Venue TBD' }}
                                    </p>
                                </div>
                            </div>
                            @if ($assignment !== null)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-bold tracking-widest uppercase {{ $assignment->attendance_status === 'accepted' ? 'border-emerald-300/35 bg-emerald-500/10 text-emerald-400' : ($assignment->attendance_status === 'declined' ? 'border-rose-300/35 bg-rose-500/10 text-rose-400' : 'border-amber-300/35 bg-amber-500/10 text-amber-400') }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $assignment->attendance_status === 'accepted' ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]' : ($assignment->attendance_status === 'declined' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                        {{ $assignment->attendance_status }}
                                    </span>
                                    @if ($assignment->is_starter)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-300/35 bg-cyan-500/10 px-3 py-1 text-[10px] font-bold tracking-widest uppercase text-cyan-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                            Starter
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if ($assignment !== null)
                            <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-white/5 pt-4">
                                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Assigned by: {{ $assignment->assignedBy?->name ?? 'Team Coach' }}</p>
                                
                                @if ($canRespondAttendance)
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ route('tenant.player.assignments.attendance.update', $assignment) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="attendance_status" value="accepted">
                                            <button type="submit" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500 hover:scale-[1.02] active:scale-95">
                                                Confirm Participation
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('tenant.player.assignments.attendance.update', $assignment) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="attendance_status" value="declined">
                                            <button type="submit" class="flex items-center gap-2 rounded-lg bg-rose-600/20 border border-rose-500/30 px-4 py-2 text-xs font-bold text-rose-100 transition hover:bg-rose-500/30">
                                                Decline Match
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="rounded-lg bg-amber-500/10 p-2 border border-amber-500/20 text-[10px] font-bold text-amber-200">
                                        ATTENDANCE RESPONSE DISABLED
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-6 rounded-xl bg-slate-950/40 p-4 border border-white/5 text-center text-xs text-slate-500 italic">
                                Your lineup assignment for this match is still being finalized by the coaching staff.
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-12 text-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h4 class="mt-4 text-lg font-bold text-slate-300">No Upcoming Matches</h4>
                        <p class="mt-2 text-sm text-slate-500">There are no upcoming games scheduled for your team at this time.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section x-show="activeTab === 'team'" class="space-y-6" role="tabpanel" x-cloak>
            @if ($canViewRoster)
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Team Roster</h3>
                        <p class="text-sm text-slate-400">Connect with your teammates and view active status.</p>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85 shadow-xl backdrop-blur-md">
                        <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                            <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">Teammate</th>
                                    <th class="px-6 py-4 text-left font-semibold">Position</th>
                                    <th class="px-6 py-4 text-center font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @forelse ($teamRoster as $teamPlayer)
                                    <tr class="hover:bg-white/5 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-8 w-8 rounded-full border border-white/10 bg-slate-800 overflow-hidden">
                                                    @php $tmPhoto = $profilePhotoUrl($teamPlayer->user?->profile_photo_path); @endphp
                                                    @if ($tmPhoto)
                                                        <img src="{{ $tmPhoto }}" class="h-full w-full object-cover" />
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center bg-slate-700 text-[10px] font-bold text-slate-500">
                                                            {{ strtoupper(substr($teamPlayer->first_name, 0, 1)) }}{{ strtoupper(substr($teamPlayer->last_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="font-semibold text-slate-100">{{ $teamPlayer->last_name }}, {{ $teamPlayer->first_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-300">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="h-1 w-1 rounded-full bg-cyan-400"></span>
                                                {{ $teamPlayer->position ?: 'Unassigned' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-widest uppercase {{ $teamPlayer->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-500/20' }}">
                                                {{ $teamPlayer->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-500">No roster data available for your team.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($canViewAnnouncements)
                <div class="space-y-4 pt-6 border-t border-white/5">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Team Announcements
                        </h3>
                        <p class="text-sm text-slate-400">Latest messages from your coach and team staff.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse ($announcements as $announcement)
                            <article class="relative overflow-hidden rounded-2xl border border-white/5 bg-white/[0.03] p-5 shadow-lg transition hover:bg-white/[0.05]">
                                <h4 class="text-base font-bold text-slate-100">{{ $announcement->title }}</h4>
                                <p class="mt-2 text-sm leading-relaxed text-slate-300 line-clamp-3">{{ $announcement->body }}</p>
                                <div class="mt-4 flex items-center justify-between text-[10px] uppercase tracking-widest font-bold">
                                    <span class="text-slate-500">{{ $announcement->published_at?->format('M d, Y') }}</span>
                                    <span class="text-cyan-400">{{ $announcement->published_at?->format('h:i A') }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2 rounded-2xl border border-white/5 bg-slate-900/40 p-12 text-center text-slate-500">
                                No announcements posted to your team yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>

        <section x-show="activeTab === 'history'" class="space-y-6" role="tabpanel" x-cloak>
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-100">Team Results History</h3>
                <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85 shadow-xl backdrop-blur-md">
                    <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                        <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Matchup</th>
                                <th class="px-6 py-4 text-center font-semibold">Final Score</th>
                                <th class="px-6 py-4 text-left font-semibold">Venue</th>
                                <th class="px-6 py-4 text-right font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse ($recentResults as $game)
                                <tr class="hover:bg-white/5 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-100">
                                        {{ $game->homeTeam?->name ?? 'TBD' }} vs {{ $game->awayTeam?->name ?? 'TBD' }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-cyan-400">{{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}</td>
                                    <td class="px-6 py-4 text-slate-300 text-xs">{{ $game->venue?->name ?? 'No venue' }}</td>
                                    <td class="px-6 py-4 text-right text-slate-400 font-mono text-xs">{{ $game->scheduled_at?->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-500">No completed match results found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($canViewHistory)
                <div class="space-y-4 pt-6 border-t border-white/5">
                    <h3 class="text-lg font-semibold text-slate-100">My Participation History</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($myCompletedMatches as $assignment)
                            @php $game = $assignment->game; @endphp
                            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 shadow-lg">
                                <div class="flex items-center justify-between mb-3 border-b border-white/5 pb-3">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $game?->scheduled_at?->format('M d, Y') }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-widest bg-slate-500/10 text-slate-400 border border-white/10">FINAL</span>
                                </div>
                                <p class="text-sm font-bold text-slate-100 truncate">
                                    {{ $game?->homeTeam?->name ?? 'TBD' }} vs {{ $game?->awayTeam?->name ?? 'TBD' }}
                                </p>
                                <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                                    <span class="text-[10px] text-slate-500 font-bold uppercase">Attendance</span>
                                    <span class="text-[10px] font-black text-emerald-400 uppercase">{{ $assignment->attendance_status }}</span>
                                </div>
                            </article>
                        @empty
                            <div class="lg:col-span-3 rounded-2xl border border-white/5 bg-slate-900/40 p-12 text-center text-slate-500">
                                No personal participation history available.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
