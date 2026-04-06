<x-app-layout>
    @php
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
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Teams</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('tenant.teams.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Add Team</a>
        </div>

        <div class="mb-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4">
            <label for="teams-search" class="mb-2 block text-xs uppercase tracking-[0.14em] text-slate-400">Search Teams</label>
            <input id="teams-search" type="text" placeholder="Search by team, sport, or division" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Team</th>
                        <th class="px-4 py-3 text-left font-medium">Sport</th>
                        <th class="px-4 py-3 text-left font-medium">Division</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($teams as $team)
                        <tr data-searchable-row data-search-text="{{ strtolower($team->name.' '.($team->sport?->name ?? '').' '.($team->division ?? '')) }}">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $team->name }}</p>
                                <span class="mt-1 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] {{ $sportBadge($team->sport?->name) }}">
                                    {{ $team->sport?->name ?? 'No Sport' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $team->sport?->name }}</td>
                            <td class="px-4 py-3">{{ $team->division ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $team->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('tenant.teams.edit', $team) }}" class="rounded-md border border-slate-300/40 bg-slate-500/20 px-3 py-1 text-xs text-slate-100 hover:bg-slate-500/30">Edit</a>
                                    <form method="POST" action="{{ route('tenant.teams.destroy', $team) }}" onsubmit="return confirm('Delete this team?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No teams available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('teams-search');
            if (!input) return;

            const rows = Array.from(document.querySelectorAll('[data-searchable-row]'));

            input.addEventListener('input', () => {
                const query = input.value.trim().toLowerCase();
                rows.forEach((row) => {
                    const text = row.getAttribute('data-search-text') || '';
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        })();
    </script>
</x-app-layout>
