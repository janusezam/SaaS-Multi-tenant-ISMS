<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">{{ $team->name }}</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-slate-900/85 overflow-hidden shadow-lg shadow-slate-950/40">
                    @if($team->logo_path)
                        @php
                            $logoPath = str_replace('\\', '/', trim((string) $team->logo_path));
                            $logoPath = ltrim($logoPath, '/');
                            $logoPath = preg_replace('#^(public/)+#', '', $logoPath) ?? $logoPath;
                            $logoPath = preg_replace('#^(storage/)+#', '', $logoPath) ?? $logoPath;
                        @endphp
                        <img src="{{ tenant_asset($logoPath) }}" class="h-full w-full object-cover" />
                    @else
                        <span class="text-2xl font-bold text-slate-400">{{ substr($team->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-100">{{ $team->name }}</h3>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="inline-flex rounded-full border border-cyan-300/40 bg-cyan-500/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-cyan-100">
                            {{ $team->sport?->name }}
                        </span>
                        <span class="text-sm text-slate-400">•</span>
                        <span class="text-sm text-slate-400">{{ $team->division ?? 'No Division' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('tenant.teams.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10">Back to List</a>
                <a href="{{ route('tenant.teams.edit', $team) }}" class="rounded-xl border border-slate-300/40 bg-slate-500/20 px-4 py-2 text-sm font-medium text-slate-100 hover:bg-slate-500/30">Edit Team</a>
            </div>
        </div>
        <div class="mb-8 grid gap-6 lg:grid-cols-4">
            <!-- Sidebar Stats/Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Team Details</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-xs text-slate-500">Coach</label>
                            <p class="font-medium text-slate-200">{{ $team->coach_name ?: 'Not Assigned' }}</p>
                            @if($team->coach_email)
                                <p class="text-xs text-slate-400">{{ $team->coach_email }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-slate-500">Status</label>
                            <div>
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $team->is_active ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-slate-300/40 bg-slate-500/20 text-slate-100' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-center">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Roster Count</h3>
                    <p class="mt-2 text-4xl font-bold text-white">{{ $players->count() }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Active Players</p>
                </div>
            </div>

            <!-- Roster Table -->
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 overflow-hidden">
                    <div class="border-b border-white/10 bg-slate-950/40 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">Team Roster</h3>
                    </div>
                    <table class="min-w-full divide-y divide-white/10">
                        <thead class="bg-slate-950/60">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse ($players as $player)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 shrink-0 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 font-bold border border-white/10">
                                                {{ substr($player->first_name, 0, 1) }}{{ substr($player->last_name, 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-100">{{ $player->first_name }} {{ $player->last_name }}</div>
                                                <div class="text-xs text-slate-400">{{ $player->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                        {{ $player->student_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                                        {{ $player->position ?: 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $player->is_active ? 'text-emerald-400' : 'text-slate-400' }}">
                                            {{ $player->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                        No players assigned to this team.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
