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

        $selectedSport = (string) request('sport', 'all');

        $sportTabs = [
            'all' => 'All Sports',
            'basketball' => 'Basketball',
            'volleyball' => 'Volleyball',
            'football' => 'Football',
            'badminton' => 'Badminton',
        ];

        $statusClasses = [
            'scheduled' => 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100',
            'completed' => 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100',
            'cancelled' => 'border-rose-300/40 bg-rose-500/20 text-rose-100',
        ];

        $sportClasses = [
            'basketball' => 'border-orange-300/40 bg-orange-500/20 text-orange-100',
            'volleyball' => 'border-indigo-300/40 bg-indigo-500/20 text-indigo-100',
            'football' => 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100',
            'badminton' => 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100',
        ];

        $sportBadge = static function (?string $sportName) use ($sportClasses): string {
            $key = strtolower((string) $sportName);

            foreach ($sportClasses as $sportKey => $classes) {
                if (str_contains($key, $sportKey)) {
                    return $classes;
                }
            }

            return 'border-slate-300/40 bg-slate-500/20 text-slate-100';
        };

        $gamesCollection = $games->getCollection();

        if ($selectedSport !== 'all') {
            $gamesCollection = $gamesCollection->filter(function ($game) use ($selectedSport): bool {
                return str_contains(strtolower((string) $game->sport?->name), $selectedSport);
            })->values();
        }

        $today = now()->toDateString();
        $todayGames = $gamesCollection
            ->filter(fn ($game): bool => $game->scheduled_at?->toDateString() === $today && $game->status !== 'completed')
            ->sortBy('scheduled_at')
            ->values();

        $upcomingGames = $gamesCollection
            ->filter(fn ($game): bool => $game->scheduled_at?->toDateString() > $today && $game->status !== 'completed')
            ->sortBy('scheduled_at')
            ->values();

        $completedGames = $gamesCollection
            ->filter(fn ($game): bool => $game->status === 'completed')
            ->sortByDesc('scheduled_at')
            ->values();
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Schedules</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('tenant.games.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Schedule Game</a>
        </div>

        <div class="flex flex-wrap gap-2 rounded-2xl border border-white/10 bg-slate-900/85 p-3">
            @foreach ($sportTabs as $tabKey => $tabLabel)
                <a
                    href="{{ route('tenant.games.index', ['sport' => $tabKey]) }}"
                    class="rounded-lg border px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition {{ $selectedSport === $tabKey ? 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10' }}"
                >
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        @php
            $sections = [
                'Today' => $todayGames,
                'Upcoming' => $upcomingGames,
                'Completed' => $completedGames,
            ];
        @endphp

        @foreach ($sections as $label => $sectionGames)
            <section class="space-y-3">
                <h3 class="text-lg font-semibold text-slate-100">{{ $label }}</h3>

                @if ($sectionGames->isEmpty())
                    <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-sm text-slate-400">
                        No {{ strtolower($label) }} games found for this sport filter.
                    </div>
                @else
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($sectionGames as $game)
                            <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sportBadge($game->sport?->name) }}">
                                        {{ $game->sport?->name ?? 'Sport' }}
                                    </span>

                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$game->status] ?? 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                        {{ strtoupper($game->status) }}
                                    </span>
                                </div>

                                <p class="mt-3 text-xs text-slate-400">{{ $game->scheduled_at?->format('M d, Y h:i A') }} · {{ $game->venue?->name ?? 'No venue' }}</p>

                                <div class="mt-3 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $homeName = $game->homeTeam?->name ?? 'TBD Team';
                                            $homeLogo = $teamLogoUrl($game->homeTeam?->logo_path);
                                        @endphp
                                        @if ($homeLogo !== null)
                                            <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="h-10 w-10 rounded-full border border-white/15 object-cover" />
                                        @else
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xs font-semibold text-slate-300">{{ strtoupper(substr((string) $homeName, 0, 1)) }}</span>
                                        @endif
                                        <p class="text-sm font-medium text-slate-100">{{ $homeName }}</p>
                                    </div>

                                    @if ($game->status === 'completed')
                                        <p class="rounded-lg border border-emerald-300/35 bg-emerald-500/20 px-3 py-1 text-sm font-semibold text-emerald-100">
                                            {{ $game->home_score ?? 0 }} - {{ $game->away_score ?? 0 }}
                                        </p>
                                    @else
                                        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">VS</p>
                                    @endif

                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $awayName = $game->awayTeam?->name ?? 'TBD Team';
                                            $awayLogo = $teamLogoUrl($game->awayTeam?->logo_path);
                                        @endphp
                                        <p class="text-right text-sm font-medium text-slate-100">{{ $awayName }}</p>
                                        @if ($awayLogo !== null)
                                            <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="h-10 w-10 rounded-full border border-white/15 object-cover" />
                                        @else
                                            <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xs font-semibold text-slate-300">{{ strtoupper(substr((string) $awayName, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 space-y-2">
                                    @if (in_array(auth()->user()->role, ['university_admin', 'sports_facilitator'], true) && $game->status !== 'completed')
                                        <form method="POST" action="{{ route('tenant.games.result', $game) }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-cyan-300/20 bg-cyan-500/10 p-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed" />
                                            <input type="number" min="0" name="home_score" value="{{ $game->home_score ?? '' }}" class="w-16 rounded border border-white/10 bg-slate-950/60 px-2 py-1 text-xs text-slate-100" placeholder="H" />
                                            <input type="number" min="0" name="away_score" value="{{ $game->away_score ?? '' }}" class="w-16 rounded border border-white/10 bg-slate-950/60 px-2 py-1 text-xs text-slate-100" placeholder="A" />
                                            <button type="submit" class="rounded border border-cyan-300/30 bg-cyan-500/20 px-2 py-1 text-xs text-cyan-100 hover:bg-cyan-500/30">Submit Result</button>
                                        </form>
                                    @elseif ($game->status === 'completed')
                                        <p class="rounded-xl border border-emerald-300/20 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-100">
                                            Result already submitted. You can still use game actions below.
                                        </p>
                                    @endif

                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('tenant.games.audits', $game) }}" class="rounded-md border border-amber-300/30 bg-amber-500/15 px-3 py-1 text-xs text-amber-100 hover:bg-amber-500/25">Audit Trail</a>
                                        <a href="{{ route('tenant.games.edit', $game) }}" class="rounded-md border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-200 hover:bg-white/10">Edit</a>
                                        <form method="POST" action="{{ route('tenant.games.destroy', $game) }}" onsubmit="return confirm('Delete this scheduled game?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach

        <div>
            {{ $games->appends(['sport' => $selectedSport])->links() }}
        </div>
    </div>
</x-app-layout>
