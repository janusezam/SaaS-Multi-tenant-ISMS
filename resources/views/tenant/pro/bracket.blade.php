<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-slate-100">Bracket Generator</h2>
            @if (! $isLocked)
                <a href="{{ route('tenant.pro.bracket.audits', ['sport_id' => $selectedSportId]) }}" class="rounded-xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-2 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">Bracket Audits</a>
            @else
                <span class="rounded-full border border-amber-300/40 bg-amber-500/20 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-100">Locked on Basic</span>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="relative overflow-hidden rounded-2xl">
        <div class="space-y-5 {{ $isLocked ? 'pointer-events-none select-none blur-[1px]' : '' }}">
        <form method="GET" action="{{ route('tenant.pro.bracket') }}" class="grid gap-4 rounded-2xl border border-white/10 bg-slate-900/85 p-4 sm:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm text-slate-300" for="sport_id">Sport</label>
                <select id="sport_id" name="sport_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100">
                    <option value="">All Sports</option>
                    @foreach ($sports as $sport)
                        <option value="{{ $sport->id }}" @selected((string) $selectedSportId === (string) $sport->id)>{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Generate</button>
            </div>
        </form>

        @if ($selectedSportId)
            <form method="POST" action="{{ route('tenant.pro.bracket.generate') }}" class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                @csrf
                <input type="hidden" name="sport_id" value="{{ $selectedSportId }}" />
                <button type="submit" class="rounded-xl border border-amber-300/40 bg-amber-500/20 px-4 py-2 text-sm font-medium text-amber-100 hover:bg-amber-500/30">
                    {{ $hasStoredBracket ? 'Regenerate Knockout Bracket' : 'Persist Knockout Bracket' }}
                </button>
            </form>
        @endif

        <div class="rounded-2xl border border-white/10 bg-slate-900/85 px-4 py-3 text-sm text-slate-300">
            @if ($selectedSportId)
                @php
                    $selectedSportName = optional($sports->firstWhere('id', (int) $selectedSportId))->name ?? 'Selected Sport';
                @endphp
                Viewing bracket for <span class="font-semibold text-cyan-200">{{ $selectedSportName }}</span>
            @else
                Viewing <span class="font-semibold text-cyan-200">All Sports Preview</span>. Choose a sport above to persist or regenerate a specific bracket.
            @endif
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            @forelse ($rounds as $round)
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-300">{{ $round['name'] }}</p>

                    <div class="mt-3 space-y-3">
                        @foreach ($round['matches'] as $index => $match)
                            <div class="rounded-xl border border-white/10 bg-slate-950/60 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[10px] uppercase tracking-[0.18em] text-slate-400">Match {{ $index + 1 }}</p>
                                    @php
                                        $isCompleted = !empty($match['winner']);
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] {{ $isCompleted ? 'border-emerald-300/40 bg-emerald-500/20 text-emerald-100' : 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' }}">
                                        {{ $isCompleted ? 'Completed' : 'Scheduled' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-100">{{ $match['home'] }}</p>
                                <p class="text-xs text-slate-500">vs</p>
                                <p class="text-sm text-slate-100">{{ $match['away'] }}</p>

                                @if (($hasStoredBracket ?? false) && isset($match['id']))
                                    @if (!empty($match['winner']))
                                        <p class="mt-2 rounded-lg border border-emerald-300/35 bg-emerald-500/20 px-2 py-1 text-xs font-semibold text-emerald-100">Winner: {{ $match['winner'] }}</p>
                                    @elseif (!empty($match['home_team_id']) && !empty($match['away_team_id']))
                                        <form method="POST" action="{{ route('tenant.pro.bracket.matches.winner', $match['id']) }}" class="mt-2 flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="winner_team_id" class="rounded border border-white/10 bg-slate-900/80 px-2 py-1 text-xs text-slate-100">
                                                <option value="{{ $match['home_team_id'] }}">{{ $match['home'] }}</option>
                                                <option value="{{ $match['away_team_id'] }}">{{ $match['away'] }}</option>
                                            </select>
                                            <button type="submit" class="rounded border border-cyan-300/30 bg-cyan-500/20 px-2 py-1 text-xs text-cyan-100 hover:bg-cyan-500/30">Set Winner</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-300 lg:col-span-3">
                    No teams available to generate a bracket.
                </div>
            @endforelse
        </div>
        </div>

        @if ($isLocked)
            <div class="pro-lock-overlay absolute inset-0 flex items-center justify-center p-6">
                <div class="pro-lock-card max-w-xl rounded-2xl p-6 text-center">
                    <p class="text-xs uppercase tracking-[0.2em] text-amber-200">Subscription Lock</p>
                    <h3 class="pro-lock-title mt-2 text-xl font-semibold">Upgrade to Pro to unlock Brackets</h3>
                    <p class="pro-lock-copy mt-2 text-sm">You can preview the knockout layout in the background, but creating and managing brackets needs Pro access.</p>
                    <button type="button" data-upgrade-trigger class="mt-4 rounded-xl border border-emerald-300/35 bg-emerald-500/20 px-4 py-2 text-sm font-medium text-emerald-100 hover:bg-emerald-500/30">
                        Request Upgrade
                    </button>
                    <p class="pro-lock-note mt-3 text-xs">Pro access is managed by central subscription settings.</p>
                </div>
            </div>
        @endif
        </div>
    </div>
</x-app-layout>
