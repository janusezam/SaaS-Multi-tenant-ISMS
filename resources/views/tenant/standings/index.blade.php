<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Live Standings</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('tenant.standings.index') }}" class="grid gap-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="sport_id">Sport</label>
                <select id="sport_id" name="sport_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100">
                    <option value="">All Sports</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected((string) $selectedSportId === (string) $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="division">Division</label>
                <input id="division" name="division" value="{{ $selectedDivision }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" placeholder="A, B, College, etc." />
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Apply</button>
                <a href="{{ route('tenant.standings.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
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
                    @forelse ($standings as $index => $row)
                        <tr>
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">{{ $row['team'] }}</td>
                            <td class="px-4 py-3">{{ $row['played'] }}</td>
                            <td class="px-4 py-3">{{ $row['wins'] }}</td>
                            <td class="px-4 py-3">{{ $row['draws'] }}</td>
                            <td class="px-4 py-3">{{ $row['losses'] }}</td>
                            <td class="px-4 py-3">{{ $row['gf'] }}</td>
                            <td class="px-4 py-3">{{ $row['ga'] }}</td>
                            <td class="px-4 py-3">{{ $row['gd'] }}</td>
                            <td class="px-4 py-3 font-semibold text-cyan-200">{{ $row['points'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-6 text-center text-slate-400">No completed games available for standings.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
