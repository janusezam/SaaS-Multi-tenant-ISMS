<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">Sports</h2>
            <p class="mt-1 text-sm text-slate-300">Classroom-style sport cards with customizable cover photos.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('tenant.sports.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Add Sport</a>
        </div>

        <div class="mb-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4">
            <label for="sports-search" class="mb-2 block text-xs uppercase tracking-[0.14em] text-slate-400">Search Sports</label>
            <input id="sports-search" type="text" placeholder="Search by name or code" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
        </div>

        <div id="sports-cards" class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($sports as $sport)
                <article data-searchable-card data-search-text="{{ strtolower($sport->name.' '.$sport->code) }}" class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 shadow-lg shadow-slate-950/20">
                    <div class="relative h-36 border-b border-white/10 bg-gradient-to-r from-cyan-700/80 via-sky-600/80 to-indigo-700/80">
                        @if (! empty($sport->cover_photo_path))
                            @php
                                $normalizedCoverPath = str_replace('\\', '/', trim((string) $sport->cover_photo_path));
                                $normalizedCoverPath = ltrim($normalizedCoverPath, '/');
                                $normalizedCoverPath = preg_replace('#^(public/)+#', '', $normalizedCoverPath) ?? $normalizedCoverPath;
                                $normalizedCoverPath = preg_replace('#^(storage/)+#', '', $normalizedCoverPath) ?? $normalizedCoverPath;
                            @endphp
                            <img src="{{ tenant_asset($normalizedCoverPath) }}" alt="{{ $sport->name }}" class="h-full w-full object-cover" />
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"></div>
                        <div class="absolute left-4 right-4 top-3 flex items-start justify-between">
                            <span class="rounded-full border border-white/30 bg-slate-900/40 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-100">{{ $sport->code }}</span>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $sport->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">{{ $sport->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <h3 class="absolute bottom-3 left-4 right-4 truncate text-lg font-semibold text-white">{{ $sport->name }}</h3>
                    </div>

                    <div class="space-y-4 p-4">
                        <p class="line-clamp-2 text-sm text-slate-300">{{ $sport->description ?: 'No description yet for this sport.' }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('tenant.sports.show', $sport) }}" class="rounded-md border border-cyan-300/40 bg-cyan-500/20 px-3 py-1.5 text-xs text-cyan-100 hover:bg-cyan-500/30">View</a>
                            <a href="{{ route('tenant.sports.edit', $sport) }}" class="rounded-md border border-slate-300/40 bg-slate-500/20 px-3 py-1.5 text-xs text-slate-100 hover:bg-slate-500/30">Edit</a>
                            <form method="POST" action="{{ route('tenant.sports.destroy', $sport) }}" onsubmit="return confirm('Delete this sport?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1.5 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 px-5 py-8 text-center text-slate-400 sm:col-span-2 xl:col-span-3">No sports available.</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $sports->links() }}
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('sports-search');
            if (!input) return;

            const cards = Array.from(document.querySelectorAll('[data-searchable-card]'));

            input.addEventListener('input', () => {
                const query = input.value.trim().toLowerCase();
                cards.forEach((card) => {
                    const text = card.getAttribute('data-search-text') || '';
                    card.style.display = text.includes(query) ? '' : 'none';
                });
            });
        })();
    </script>
</x-app-layout>
