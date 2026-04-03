<x-app-layout>
    @php
        $selectedSport = (string) request('sport', 'all');
        $selectedDivision = (string) request('division', $selectedDivision ?? '');

        $sportTabs = [
            'all' => 'All Sports',
            'basketball' => 'Basketball',
            'volleyball' => 'Volleyball',
            'football' => 'Football',
            'badminton' => 'Badminton',
        ];

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
        $activeSports = \App\Models\Sport::query()->where('is_active', true)->orderBy('name')->get();
        $completedGames = \App\Models\Game::query()->with(['sport', 'homeTeam', 'awayTeam'])->where('status', 'completed')->get();

        if ($selectedDivision !== '') {
            $completedGames = $completedGames->filter(function (\App\Models\Game $game) use ($selectedDivision): bool {
                return ($game->homeTeam?->division === $selectedDivision) && ($game->awayTeam?->division === $selectedDivision);
            })->values();
        }

        $sportsToRender = $activeSports->filter(function (\App\Models\Sport $sport) use ($selectedSport): bool {
            if ($selectedSport === 'all') {
                return true;
            }

            return str_contains(strtolower($sport->name), $selectedSport);
        })->values();

        $sections = $sportsToRender->map(function (\App\Models\Sport $sport) use ($completedGames, $calculator): array {
            $sportGames = $completedGames->where('sport_id', $sport->id)->values();

            return [
                'sport' => $sport,
                'rows' => $calculator->calculate($sportGames),
            ];
        })->filter(fn (array $section): bool => ! empty($section['rows']))->values();
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Live Standings</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2 rounded-2xl border border-white/10 bg-slate-900/85 p-3">
            @foreach ($sportTabs as $tabKey => $tabLabel)
                <a
                    href="{{ route('tenant.standings.index', ['sport' => $tabKey, 'division' => $selectedDivision !== '' ? $selectedDivision : null]) }}"
                    class="rounded-lg border px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition {{ $selectedSport === $tabKey ? 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-300 hover:bg-white/10' }}"
                >
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('tenant.standings.index') }}" class="grid gap-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-3">
            <input type="hidden" name="sport" value="{{ $selectedSport }}" />
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="division">Division</label>
                <input id="division" name="division" value="{{ $selectedDivision }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" placeholder="A, B, College, etc." />
            </div>
            <div class="flex items-end gap-2 sm:col-span-2">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Apply</button>
                <a href="{{ route('tenant.standings.index', ['sport' => $selectedSport]) }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</a>
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
                                <tr class="{{ $rankClasses }}">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-full border border-white/15 bg-white/5 px-2 py-0.5 text-xs font-semibold text-slate-100">{{ $rank }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $row['team'] }}</td>
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
