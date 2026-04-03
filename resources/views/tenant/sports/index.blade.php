<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Sports</h2>
            <a href="{{ route('tenant.sports.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Add Sport</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4">
            <label for="sports-search" class="mb-2 block text-xs uppercase tracking-[0.14em] text-slate-400">Search Sports</label>
            <input id="sports-search" type="text" placeholder="Search by name or code" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Code</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($sports as $sport)
                        <tr data-searchable-row data-search-text="{{ strtolower($sport->name.' '.$sport->code) }}">
                            <td class="px-4 py-3">{{ $sport->name }}</td>
                            <td class="px-4 py-3 uppercase">{{ $sport->code }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $sport->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                    {{ $sport->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('tenant.sports.edit', $sport) }}" class="rounded-md border border-slate-300/40 bg-slate-500/20 px-3 py-1 text-xs text-slate-100 hover:bg-slate-500/30">Edit</a>
                                    <form method="POST" action="{{ route('tenant.sports.destroy', $sport) }}" onsubmit="return confirm('Delete this sport?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">No sports available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('sports-search');
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
