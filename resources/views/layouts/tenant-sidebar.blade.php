@php
    $user = Auth::user();
    $isUniversityAdmin = $user?->hasTenantRole('university_admin') === true;
    $isFacilitator = $user?->hasTenantRole('sports_facilitator') === true;
    $isCoach = $user?->hasTenantRole('team_coach') === true;
    $isPlayer = $user?->hasTenantRole('student_player') === true;
    $permissionMatrix = app(\App\Support\TenantPermissionMatrix::class);
    $canFacilitatorManageVenues = $permissionMatrix->allows($user, 'facilitator.venues.manage');
    $canFacilitatorManageGames = $permissionMatrix->allows($user, 'facilitator.games.manage');
    $canFacilitatorAuditResults = $permissionMatrix->allows($user, 'facilitator.results.audit');
    $canCoachViewSchedules = $permissionMatrix->allows($user, 'coach.schedules.view');
    $canCoachViewTeam = $permissionMatrix->allows($user, 'coach.team.view');
    $canPlayerViewSchedule = $permissionMatrix->allows($user, 'player.schedule.view');
    $tenantHasAnalytics = tenant() !== null && tenant()->hasFeature('analytics');
    $tenantHasBracket = tenant() !== null && tenant()->hasFeature('bracket');
    $workspaceLabel = match (true) {
        $isUniversityAdmin => 'Tenant Admin',
        $isFacilitator => 'Sports Facilitator',
        $isCoach => 'Team Coach',
        $isPlayer => 'Student Player',
        default => 'Tenant Workspace',
    };
@endphp

<aside class="isms-sidebar hidden border-r md:sticky md:top-0 md:flex md:h-screen md:w-72 md:shrink-0 md:flex-col md:self-start md:overflow-y-auto">
    <div class="flex h-16 items-center gap-3 border-b px-5" style="border-color: var(--isms-stroke);">
        <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center gap-2">
            <x-application-logo class="block h-8 w-auto fill-current text-cyan-300" />
            <span class="text-sm font-semibold tracking-wide isms-text">{{ $workspaceLabel }}</span>
        </a>
    </div>

    <div class="px-4 py-4 text-xs uppercase tracking-[0.2em] isms-text-muted">
        {{ tenant()?->name ?? 'Tenant' }}
    </div>

    <nav class="flex-1 space-y-1 px-3 pb-4 text-sm">
        <a href="{{ route('tenant.dashboard') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.dashboard') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Dashboard</a>

        <a href="{{ route('tenant.standings.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.standings.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Standings</a>

        @if ($isUniversityAdmin)
            <a href="{{ route('tenant.users.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.users.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Users</a>
        @endif

        @if ($isUniversityAdmin)
            <a href="{{ route('tenant.sports.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.sports.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Sports</a>

            <a href="{{ route('tenant.teams.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.teams.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Teams</a>

            <a href="{{ route('tenant.players.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.players.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Players</a>

            @if (! $tenantHasAnalytics)
                <div class="flex items-center justify-between rounded-lg border border-transparent px-3 py-2 isms-sidebar-link hover:bg-white/5">
                    <a href="{{ route('tenant.pro.analytics') }}" class="flex-1">Analytics</a>
                    <a href="{{ route('tenant.subscription.show', ['openUpgrade' => 1]) }}" class="rounded-full border border-amber-300/40 bg-amber-500/20 px-2 py-0.5 text-[10px] uppercase tracking-[0.14em] text-amber-100">Upgrade</a>
                </div>
            @else
                <a href="{{ route('tenant.pro.analytics') }}" class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('tenant.pro.analytics') ? 'bg-emerald-500/20 text-emerald-100 border border-emerald-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">
                    <span>Analytics</span>
                </a>
            @endif

            @if (! $tenantHasBracket)
                <div class="flex items-center justify-between rounded-lg border border-transparent px-3 py-2 isms-sidebar-link hover:bg-white/5">
                    <a href="{{ route('tenant.pro.bracket') }}" class="flex-1">Bracket</a>
                    <a href="{{ route('tenant.subscription.show', ['openUpgrade' => 1]) }}" class="rounded-full border border-amber-300/40 bg-amber-500/20 px-2 py-0.5 text-[10px] uppercase tracking-[0.14em] text-amber-100">Upgrade</a>
                </div>
            @else
                <a href="{{ route('tenant.pro.bracket') }}" class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('tenant.pro.bracket*') ? 'bg-emerald-500/20 text-emerald-100 border border-emerald-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">
                    <span>Bracket</span>
                </a>
            @endif
        @endif

        @if (($isUniversityAdmin || $isFacilitator) && $canFacilitatorManageVenues)
            <a href="{{ route('tenant.venues.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.venues.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Venues</a>
        @endif

        @if (($isUniversityAdmin || $isFacilitator) && $canFacilitatorManageGames)
            <a href="{{ route('tenant.games.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.games.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Schedules</a>
        @endif

        @if (($isUniversityAdmin || $isFacilitator) && $canFacilitatorAuditResults)
            <a href="{{ route('tenant.audits.game-results.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.audits.game-results.*') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Result Audits</a>
        @endif

        @if ($isCoach && $canCoachViewSchedules)
            <a href="{{ route('tenant.coach.schedules') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.coach.schedules') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">Schedules</a>
        @endif

        @if ($isCoach && $canCoachViewTeam)
            <a href="{{ route('tenant.coach.my-team') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.coach.my-team') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">My Team</a>
        @endif

        @if ($isPlayer && $canPlayerViewSchedule)
            <a href="{{ route('tenant.player.my-schedule') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('tenant.player.my-schedule') ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'isms-sidebar-link hover:bg-white/5 border border-transparent' }}">My Schedule</a>
        @endif
    </nav>

    <div class="border-t px-4 py-4" style="border-color: var(--isms-stroke);">
        <p class="mb-3 text-sm isms-text">{{ $user?->name }}</p>

        <div class="space-y-2">
            <button type="button" data-theme-toggle class="isms-theme-toggle w-full">
                <span data-theme-label>Light mode</span>
            </button>

            @if ($isUniversityAdmin)
                <a href="{{ route('tenant.rbac.index') }}" class="block rounded-lg border px-3 py-2 text-sm {{ request()->routeIs('tenant.rbac.*') ? 'border-cyan-300/30 bg-cyan-500/20 text-cyan-100' : 'isms-text hover:bg-white/10' }}" style="border-color: var(--isms-stroke); background: var(--isms-toggle-bg);">RBAC (Roles & Access)</a>
            @endif

            <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-rose-300/30 bg-rose-500/20 px-3 py-2 text-left text-sm text-rose-100 hover:bg-rose-500/30">Log Out</button>
            </form>
        </div>
    </div>
</aside>
