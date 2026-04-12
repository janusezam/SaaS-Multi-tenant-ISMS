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

        $activeSports = \App\Models\Sport::query()->where('is_active', true)->orderBy('name')->get();

        $selectedSportId = request()->integer('sport_id') ?: null;
        $legacySport = strtolower((string) request('sport', 'all'));

        if ($selectedSportId === null && $legacySport !== 'all' && $legacySport !== '') {
            $selectedSportId = $activeSports
                ->first(fn (\App\Models\Sport $sport): bool => str_contains(strtolower($sport->name), $legacySport))
                ?->id;
        }
        $selectedDivision = (string) request('division', $selectedDivision ?? '');

        $sportBadgeClasses = [
            'basketball' => 'border-orange-300/40 bg-orange-500/20 text-orange-100',
            'volleyball' => 'border-indigo-300/40 bg-indigo-500/20 text-indigo-100',
            'football' => 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100',
            'badminton' => 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100',
        ];

        $sportClass = static function (string $sportName) use ($sportBadgeClasses): string {
            $sportKey = strtolower($sportName);

            foreach ($sportBadgeClasses as $key => $classes) {
                if (str_contains($sportKey, $key)) {
                    return $classes;
                }
            }

            return 'border-slate-300/40 bg-slate-500/20 text-slate-100';
        };

        $calculator = app(\App\Support\StandingsCalculator::class);
        $completedGames = \App\Models\Game::query()->with(['sport', 'homeTeam', 'awayTeam'])->where('status', 'completed')->get();

        if ($selectedDivision !== '') {
            $completedGames = $completedGames->filter(function (\App\Models\Game $game) use ($selectedDivision): bool {
                return ($game->homeTeam?->division === $selectedDivision) && ($game->awayTeam?->division === $selectedDivision);
            })->values();
        }

        $sportsToRender = $activeSports->filter(function (\App\Models\Sport $sport) use ($selectedSportId): bool {
            if ($selectedSportId === null) {
                return true;
            }

            return $sport->id === $selectedSportId;
        })->values();

        $sections = $sportsToRender->map(function (\App\Models\Sport $sport) use ($completedGames, $calculator): array {
            $sportGames = $completedGames->where('sport_id', $sport->id)->values();

            return [
                'sport' => $sport,
                'rows' => $calculator->calculate($sportGames),
            ];
        })->filter(fn (array $section): bool => ! empty($section['rows']))->values();

        $teamLogoLookupBySport = \App\Models\Team::query()
            ->select(['sport_id', 'name', 'logo_path'])
            ->whereIn('sport_id', $sportsToRender->pluck('id')->all())
            ->get()
            ->groupBy('sport_id')
            ->map(function ($teams) {
                return $teams->mapWithKeys(function ($team): array {
                    return [strtolower(trim((string) $team->name)) => $team->logo_path];
                });
            });
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">Live Standings</h2>
            <p class="mt-1 text-sm text-slate-300">Classroom-style leaderboard view with team identities.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2 rounded-2xl border border-white/10 bg-slate-900/85 p-3">
            <a
                href="{{ route('tenant.standings.index', ['sport_id' => null, 'division' => $selectedDivision !== '' ? $selectedDivision : null]) }}"
                class="rounded-lg border px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition {{ $selectedSportId === null ? 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10' }}"
            >
                All Sports
            </a>

            @foreach ($activeSports as $sportTab)
                <a
                    href="{{ route('tenant.standings.index', ['sport_id' => $sportTab->id, 'division' => $selectedDivision !== '' ? $selectedDivision : null]) }}"
                    class="rounded-lg border px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition {{ $selectedSportId === $sportTab->id ? 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10' }}"
                >
                    {{ $sportTab->name }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('tenant.standings.index') }}" class="grid gap-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-3">
            @if ($selectedSportId !== null)
                <input type="hidden" name="sport_id" value="{{ $selectedSportId }}" />
            @endif
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="division">Division</label>
                <input id="division" name="division" value="{{ $selectedDivision }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" placeholder="A, B, College, etc." />
            </div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Apply</button>
                <a href="{{ route('tenant.standings.index', ['sport_id' => $selectedSportId]) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</a>
            </div>
        </form>

        @forelse ($sections as $section)
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
                <div class="border-b border-white/10 bg-slate-950/60 px-4 py-3">
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sportClass($section['sport']->name) }}">
                        {{ $section['sport']->name }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-sm">
                        <thead class="bg-slate-950/50 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">#</th>
                                <th class="px-4 py-3 text-left font-medium">Team</th>
                                <th class="px-4 py-3 text-left font-medium">P</th>
                                <th class="px-4 py-3 text-left font-medium">W</th>
                                <th class="px-4 py-3 text-left font-medium">D</th>
                                <th class="px-4 py-3 text-left font-medium">L</th>
                                <th class="px-4 py-3 text-left font-medium">GF</th>
                                <th class="px-4 py-3 text-left font-medium">GA</th>
                                <th class="px-4 py-3 text-left font-medium">GD</th>
                                <th class="px-4 py-3 text-left font-medium">Pts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-slate-200">
                            @foreach ($section['rows'] as $index => $row)
                                @php
                                    $rank = $index + 1;
                                    $rankClasses = match ($rank) {
                                        1 => 'border-l-4 border-amber-300 bg-amber-500/10',
                                        2 => 'border-l-4 border-slate-300 bg-white/5',
                                        3 => 'border-l-4 border-orange-300 bg-orange-500/10',
                                        default => '',
                                    };
                                @endphp
                                @php
                                    $teamName = (string) ($row['team'] ?? 'TBD Team');
                                    $logoPath = $teamLogoLookupBySport
                                        ->get($section['sport']->id)
                                        ?->get(strtolower(trim($teamName)));
                                    $teamLogoUrl = $mediaUrl($logoPath);
                                @endphp
                                <tr class="{{ $rankClasses }}">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-full border border-white/15 bg-white/5 px-2 py-0.5 text-xs font-semibold text-slate-100">{{ $rank }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <div class="flex items-center gap-2">
                                            @if ($teamLogoUrl !== null)
                                                <img src="{{ $teamLogoUrl }}" alt="{{ $teamName }}" class="h-10 w-10 rounded-full border border-white/15 object-cover" />
                                            @else
                                                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/5 text-xs font-semibold text-slate-300">{{ strtoupper(substr($teamName, 0, 1)) }}</span>
                                            @endif
                                            <span>{{ $teamName }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ $row['played'] }}</td>
                                    <td class="px-4 py-3">{{ $row['wins'] }}</td>
                                    <td class="px-4 py-3">{{ $row['draws'] }}</td>
                                    <td class="px-4 py-3">{{ $row['losses'] }}</td>
                                    <td class="px-4 py-3">{{ $row['gf'] }}</td>
                                    <td class="px-4 py-3">{{ $row['ga'] }}</td>
                                    <td class="px-4 py-3">{{ $row['gd'] }}</td>
                                    <td class="px-4 py-3 text-base font-bold text-cyan-200">{{ $row['points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center text-slate-400">
                No completed games available for standings.
            </div>
        @endforelse
    </div>
</x-app-layout>
