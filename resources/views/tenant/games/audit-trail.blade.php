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

        $homeName = $game->homeTeam?->name ?? 'Home Team';
        $awayName = $game->awayTeam?->name ?? 'Away Team';
        $homeLogo = $teamLogoUrl($game->homeTeam?->logo_path);
        $awayLogo = $teamLogoUrl($game->awayTeam?->logo_path);
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-slate-100">Game Audit Trail</h2>
                <p class="mt-1 text-sm text-slate-400">
                    <span class="inline-flex items-center gap-2">
                        @if ($homeLogo !== null)
                            <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                        @endif
                        <span>{{ $homeName }}</span>
                    </span>
                    <span class="mx-1">vs</span>
                    <span class="inline-flex items-center gap-2">
                        @if ($awayLogo !== null)
                            <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="h-9 w-9 rounded-full border border-white/15 object-cover" />
                        @endif
                        <span>{{ $awayName }}</span>
                    </span>
                    @if ($game->sport)
                        <span class="text-slate-500">|</span> {{ $game->sport->name }}
                    @endif
                </p>
            </div>
            <a href="{{ route('tenant.games.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-white/10">Back to Schedules</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Changed At</th>
                        <th class="px-4 py-3 text-left font-medium">Changed By</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Home Score</th>
                        <th class="px-4 py-3 text-left font-medium">Away Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($audits as $audit)
                        <tr>
                            <td class="px-4 py-3">{{ $audit->created_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">{{ $audit->changed_by_user_id ?? 'System' }}</td>
                            <td class="px-4 py-3">
                                {{ strtoupper($audit->previous_status) }}
                                <span class="mx-2 text-slate-500">-></span>
                                {{ strtoupper($audit->new_status) }}
                            </td>
                            <td class="px-4 py-3">{{ $audit->previous_home_score ?? '-' }} <span class="mx-1 text-slate-500">-></span> {{ $audit->new_home_score ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $audit->previous_away_score ?? '-' }} <span class="mx-1 text-slate-500">-></span> {{ $audit->new_away_score ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No result changes recorded for this game yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $audits->links() }}
        </div>
    </div>
</x-app-layout>
