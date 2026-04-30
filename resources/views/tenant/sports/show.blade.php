<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">{{ $sport->name }}</h2>
            <p class="mt-1 text-sm text-slate-300">Viewing teams registered under this sport.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6 flex gap-3">
            <a href="{{ route('tenant.sports.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10">Back to List</a>
            <a href="{{ route('tenant.sports.edit', $sport) }}" class="rounded-xl border border-slate-300/40 bg-slate-500/20 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-slate-500/30">Edit Sport</a>
        </div>
        <div class="mb-8 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85 shadow-lg">
                    <div class="relative h-48 bg-gradient-to-r from-cyan-700/80 via-sky-600/80 to-indigo-700/80">
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
                        <div class="absolute bottom-4 left-4">
                            <span class="rounded-full border border-white/30 bg-slate-900/40 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-100">{{ $sport->code }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white">Description</h3>
                        <p class="mt-2 text-sm text-slate-300">{{ $sport->description ?: 'No description available.' }}</p>
                        
                        <div class="mt-6 flex items-center justify-between border-t border-white/5 pt-4">
                            <span class="text-xs uppercase tracking-wider text-slate-400">Status</span>
                            <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $sport->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">{{ $sport->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
                    <h3 class="text-lg font-semibold text-white">Registered Teams ({{ $teams->count() }})</h3>
                    
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @forelse ($teams as $team)
                            <a href="{{ route('tenant.teams.show', $team) }}" class="group flex items-center gap-4 rounded-xl border border-white/5 bg-white/5 p-4 transition-all hover:border-cyan-300/30 hover:bg-white/10">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-slate-950/40 overflow-hidden">
                                    @if($team->logo_path)
                                        @php
                                            $logoPath = str_replace('\\', '/', trim((string) $team->logo_path));
                                            $logoPath = ltrim($logoPath, '/');
                                            $logoPath = preg_replace('#^(public/)+#', '', $logoPath) ?? $logoPath;
                                            $logoPath = preg_replace('#^(storage/)+#', '', $logoPath) ?? $logoPath;
                                        @endphp
                                        <img src="{{ tenant_asset($logoPath) }}" class="h-full w-full object-cover" />
                                    @else
                                        <span class="text-lg font-bold text-slate-400">{{ substr($team->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-medium text-slate-100 group-hover:text-cyan-300">{{ $team->name }}</h4>
                                    <p class="text-xs text-slate-400">{{ $team->division ?? 'No Division' }}</p>
                                </div>
                                <div class="ml-auto">
                                    <svg class="h-5 w-5 text-slate-500 transition-colors group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full py-8 text-center text-slate-500">
                                No teams registered for this sport yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
