@php
    $user = Auth::user();
    $canManageModules = in_array($user?->role, ['university_admin', 'sports_facilitator'], true);
    $tenantCurrentPlan = tenant()?->currentPlan();
@endphp

<aside class="isms-sidebar hidden md:flex md:w-72 md:shrink-0 md:flex-col border-r">
    <div class="flex h-16 items-center gap-3 border-b px-5" style="border-color: var(--isms-stroke);">
        <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center gap-2">
            <x-application-logo class="block h-8 w-auto fill-current text-cyan-300" />
            <span class="text-sm font-semibold tracking-wide isms-text">Tenant Admin</span>
        </a>
    </div>

    <div class="px-4 py-4 text-xs uppercase tracking-[0.2em] isms-text-muted">
        {{ tenant()?->name ?? 'Tenant' }}
    </div>

    <nav class="flex-1 space-y-1 px-3 pb-4 text-sm">
        <a href="{{ route('tenant.dashboard') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.dashboard') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Dashboard</a>

        <a href="{{ route('tenant.standings.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.standings.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Standings</a>

        @if ($canManageModules)
            <a href="{{ route('tenant.sports.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.sports.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Sports</a>

            <a href="{{ route('tenant.venues.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.venues.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Venues</a>

            <a href="{{ route('tenant.teams.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.teams.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Teams</a>

            <a href="{{ route('tenant.players.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.players.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Players</a>

            <a href="{{ route('tenant.games.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.games.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Schedules</a>

            <a href="{{ route('tenant.audits.game-results.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.audits.game-results.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Result Audits</a>

            @if ($tenantCurrentPlan === 'pro')
                <a href="{{ route('tenant.pro.analytics') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.pro.analytics') ? 'bg-emerald-500/20 text-emerald-100 border border-emerald-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Analytics</a>

                <a href="{{ route('tenant.pro.bracket') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.pro.bracket*') ? 'bg-emerald-500/20 text-emerald-100 border border-emerald-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Bracket</a>
            @endif
        @endif
    </nav>

    <div class="border-t px-4 py-4" style="border-color: var(--isms-stroke);">
        <p class="mb-3 text-sm isms-text">{{ $user?->name }}</p>

        <div class="space-y-2">
            <button type="button" data-theme-toggle class="isms-theme-toggle w-full">
                <span data-theme-label>Light mode</span>
            </button>

            <a href="{{ route('profile.edit') }}" class="block rounded-lg border px-3 py-2 text-sm isms-text hover:bg-white/10" style="border-color: var(--isms-stroke); background: var(--isms-toggle-bg);">Profile</a>

            <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-rose-300/30 bg-rose-500/20 px-3 py-2 text-left text-sm text-rose-100 hover:bg-rose-500/30">Log Out</button>
            </form>
        </div>
    </div>
</aside>
