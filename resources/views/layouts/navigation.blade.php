<nav x-data="{ open: false }" class="isms-nav relative z-50 overflow-visible border-b">
    @php
        $isCentralRequest = request()->routeIs('central.*');
        $dashboardRoute = tenant() !== null
            ? 'tenant.dashboard'
            : ($isCentralRequest ? 'central.universities.index' : 'dashboard');
        $authenticatedUser = $isCentralRequest ? Auth::guard('super_admin')->user() : Auth::user();
        $logoutRoute = $isCentralRequest
            ? 'central.logout'
            : (tenant() !== null ? 'tenant.logout' : 'logout');
        $profileRoute = tenant() !== null ? 'tenant.profile.edit' : 'profile.edit';
        $tenantHasAnalytics = tenant() !== null && tenant()->hasFeature('analytics');
        $tenantHasBracket = tenant() !== null && tenant()->hasFeature('bracket');
        $canManageTenantUsers = tenant() !== null && ($authenticatedUser?->hasTenantRole('university_admin') === true);
        $isUniversityAdmin = tenant() !== null && ($authenticatedUser?->hasTenantRole('university_admin') === true);
        $isFacilitator = tenant() !== null && ($authenticatedUser?->hasTenantRole('sports_facilitator') === true);
        $isCoach = tenant() !== null && ($authenticatedUser?->hasTenantRole('team_coach') === true);
        $isPlayer = tenant() !== null && ($authenticatedUser?->hasTenantRole('student_player') === true);
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route($dashboardRoute) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-cyan-300" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if (! $isCentralRequest)
                        <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                            <span class="isms-text">{{ __('Dashboard') }}</span>
                        </x-nav-link>
                    @endif

                    @if ($isCentralRequest)
                        <x-nav-link :href="route('central.universities.index')" :active="request()->routeIs('central.universities.*')">
                            <span class="isms-text">School Management</span>
                        </x-nav-link>

                        <x-nav-link :href="route('central.business-control.index')" :active="request()->routeIs('central.business-control.*')">
                            <span class="isms-text">Business Control</span>
                        </x-nav-link>

                        <x-nav-link :href="route('central.business-control.upgrade-requests.index')" :active="request()->routeIs('central.business-control.upgrade-requests.*')">
                            <span class="isms-text">Upgrade Requests</span>
                        </x-nav-link>
                    @endif

                    @if (tenant() !== null)
                        @if ($isUniversityAdmin || $isFacilitator)
                            <x-nav-link :href="route('tenant.venues.index')" :active="request()->routeIs('tenant.venues.*')">
                                <span class="isms-text">Venues</span>
                            </x-nav-link>
                        @endif

                        @if ($isUniversityAdmin || $isFacilitator)
                            <x-nav-link :href="route('tenant.games.index')" :active="request()->routeIs('tenant.games.*')">
                                <span class="isms-text">Schedules</span>
                            </x-nav-link>

                            <x-nav-link :href="route('tenant.audits.game-results.index')" :active="request()->routeIs('tenant.audits.game-results.*')">
                                <span class="isms-text">Result Audits</span>
                            </x-nav-link>
                        @endif

                        @if ($isUniversityAdmin)
                            <x-nav-link :href="route('tenant.sports.index')" :active="request()->routeIs('tenant.sports.*')">
                                <span class="isms-text">Sports</span>
                            </x-nav-link>

                            <x-nav-link :href="route('tenant.teams.index')" :active="request()->routeIs('tenant.teams.*')">
                                <span class="isms-text">Teams</span>
                            </x-nav-link>

                            <x-nav-link :href="route('tenant.players.index')" :active="request()->routeIs('tenant.players.*')">
                                <span class="isms-text">Players</span>
                            </x-nav-link>
                        @endif

                        @if ($isCoach)
                            <x-nav-link :href="route('tenant.coach.schedules')" :active="request()->routeIs('tenant.coach.schedules')">
                                <span class="isms-text">Schedules</span>
                            </x-nav-link>

                            <x-nav-link :href="route('tenant.coach.my-team')" :active="request()->routeIs('tenant.coach.my-team')">
                                <span class="isms-text">My Team</span>
                            </x-nav-link>
                        @endif

                        @if ($isPlayer)
                            <x-nav-link :href="route('tenant.player.my-schedule')" :active="request()->routeIs('tenant.player.my-schedule')">
                                <span class="isms-text">My Schedule</span>
                            </x-nav-link>
                        @endif

                        <x-nav-link :href="route('tenant.standings.index')" :active="request()->routeIs('tenant.standings.*')">
                            <span class="isms-text">Standings</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.subscription.show')" :active="request()->routeIs('tenant.subscription.*')">
                            <span class="isms-text">Subscription</span>
                        </x-nav-link>

                        @if ($canManageTenantUsers)
                            <x-nav-link :href="route('tenant.users.index')" :active="request()->routeIs('tenant.users.*')">
                                <span class="isms-text">Users</span>
                            </x-nav-link>
                        @endif

                        @if ($isUniversityAdmin)
                            <x-nav-link :href="route('tenant.rbac.index')" :active="request()->routeIs('tenant.rbac.*')">
                                <span class="isms-text">RBAC</span>
                            </x-nav-link>
                        @endif

                        @if (($isUniversityAdmin || $isFacilitator) && $tenantHasAnalytics)
                            <x-nav-link :href="route('tenant.pro.analytics')" :active="request()->routeIs('tenant.pro.analytics')">
                                <span class="isms-text">Analytics</span>
                            </x-nav-link>
                        @endif

                        @if (($isUniversityAdmin || $isFacilitator) && $tenantHasBracket)
                            <x-nav-link :href="route('tenant.pro.bracket')" :active="request()->routeIs('tenant.pro.bracket')">
                                <span class="isms-text">Bracket</span>
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <button type="button" data-theme-toggle class="isms-theme-toggle me-3">
                    <span data-theme-label>Light mode</span>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border isms-theme-toggle focus:outline-none transition ease-in-out duration-150">
                            <div class="isms-text">{{ $authenticatedUser?->name ?? 'Account' }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (! $isCentralRequest)
                            <x-dropdown-link :href="route($profileRoute)">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route($logoutRoute) }}">
                            @csrf

                            <x-dropdown-link :href="route($logoutRoute)"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="isms-nav-link inline-flex items-center justify-center p-2 rounded-md hover:bg-white/10 focus:outline-none focus:bg-white/10 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="isms-menu-panel pt-2 pb-3 space-y-1">
            @if (! $isCentralRequest)
                <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif

            @if ($isCentralRequest)
                <x-responsive-nav-link :href="route('central.universities.index')" :active="request()->routeIs('central.universities.*')">
                    School Management
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('central.business-control.index')" :active="request()->routeIs('central.business-control.*')">
                    Business Control
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('central.business-control.upgrade-requests.index')" :active="request()->routeIs('central.business-control.upgrade-requests.*')">
                    Upgrade Requests
                </x-responsive-nav-link>
            @endif

            @if (tenant() !== null)
                @if ($isUniversityAdmin || $isFacilitator)
                    <x-responsive-nav-link :href="route('tenant.venues.index')" :active="request()->routeIs('tenant.venues.*')">
                        Venues
                    </x-responsive-nav-link>
                @endif

                @if ($isUniversityAdmin || $isFacilitator)
                    <x-responsive-nav-link :href="route('tenant.games.index')" :active="request()->routeIs('tenant.games.*')">
                        Schedules
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('tenant.audits.game-results.index')" :active="request()->routeIs('tenant.audits.game-results.*')">
                        Result Audits
                    </x-responsive-nav-link>
                @endif

                @if ($isUniversityAdmin || $isFacilitator)
                    <x-responsive-nav-link :href="route('tenant.sports.index')" :active="request()->routeIs('tenant.sports.*')">
                        Sports
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('tenant.teams.index')" :active="request()->routeIs('tenant.teams.*')">
                        Teams
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('tenant.players.index')" :active="request()->routeIs('tenant.players.*')">
                        Players
                    </x-responsive-nav-link>
                @endif

                @if ($isCoach)
                    <x-responsive-nav-link :href="route('tenant.coach.schedules')" :active="request()->routeIs('tenant.coach.schedules')">
                        Schedules
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('tenant.coach.my-team')" :active="request()->routeIs('tenant.coach.my-team')">
                        My Team
                    </x-responsive-nav-link>
                @endif

                @if ($isPlayer)
                    <x-responsive-nav-link :href="route('tenant.player.my-schedule')" :active="request()->routeIs('tenant.player.my-schedule')">
                        My Schedule
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('tenant.standings.index')" :active="request()->routeIs('tenant.standings.*')">
                    Standings
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.subscription.show')" :active="request()->routeIs('tenant.subscription.*')">
                    Subscription
                </x-responsive-nav-link>

                @if ($canManageTenantUsers)
                    <x-responsive-nav-link :href="route('tenant.users.index')" :active="request()->routeIs('tenant.users.*')">
                        Users
                    </x-responsive-nav-link>
                @endif

                @if ($isUniversityAdmin)
                    <x-responsive-nav-link :href="route('tenant.rbac.index')" :active="request()->routeIs('tenant.rbac.*')">
                        RBAC
                    </x-responsive-nav-link>
                @endif

                @if (($isUniversityAdmin || $isFacilitator) && $tenantHasAnalytics)
                    <x-responsive-nav-link :href="route('tenant.pro.analytics')" :active="request()->routeIs('tenant.pro.analytics')">
                        Analytics
                    </x-responsive-nav-link>
                @endif

                @if (($isUniversityAdmin || $isFacilitator) && $tenantHasBracket)
                    <x-responsive-nav-link :href="route('tenant.pro.bracket')" :active="request()->routeIs('tenant.pro.bracket')">
                        Bracket
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="isms-menu-panel pt-4 pb-1 border-t">
            <div class="px-4">
                <div class="font-medium text-base isms-text">{{ $authenticatedUser?->name ?? 'Account' }}</div>
                <div class="font-medium text-sm isms-text-muted">{{ $authenticatedUser?->email ?? '' }}</div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <button type="button" data-theme-toggle class="isms-theme-toggle w-full">
                    <span data-theme-label>Light mode</span>
                </button>

                @if (! $isCentralRequest)
                    <x-responsive-nav-link :href="route($profileRoute)">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route($logoutRoute) }}">
                    @csrf

                    <x-responsive-nav-link :href="route($logoutRoute)"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
