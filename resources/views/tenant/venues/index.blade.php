<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Venues</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('tenant.venues.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Add Venue</a>
        </div>

        <div class="mb-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4">
            <label for="venues-search" class="mb-2 block text-xs uppercase tracking-[0.14em] text-slate-400">Search Venues</label>
            <input id="venues-search" type="text" placeholder="Search by name or location" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Location</th>
                        <th class="px-4 py-3 text-left font-medium">Capacity</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($venues as $venue)
                        <tr data-searchable-row data-search-text="{{ strtolower($venue->name.' '.$venue->location) }}">
                            <td class="px-4 py-3">{{ $venue->name }}</td>
                            <td class="px-4 py-3">{{ $venue->location }}</td>
                            <td class="px-4 py-3">{{ number_format($venue->capacity) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $venue->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                    {{ $venue->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('tenant.venues.edit', $venue) }}" class="rounded-md border border-slate-300/40 bg-slate-500/20 px-3 py-1 text-xs text-slate-100 hover:bg-slate-500/30">Edit</a>
                                    <form method="POST" action="{{ route('tenant.venues.destroy', $venue) }}" onsubmit="return confirm('Delete this venue?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No venues available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('venues-search');
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
