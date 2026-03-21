<nav x-data="{ open: false }" class="relative z-50 overflow-visible border-b border-white/10 bg-slate-950/70 backdrop-blur">
    @php
        $isCentralRequest = request()->routeIs('central.*');
        $dashboardRoute = tenant() !== null
            ? 'tenant.dashboard'
            : ($isCentralRequest ? 'central.universities.index' : 'dashboard');
        $authenticatedUser = $isCentralRequest ? Auth::guard('super_admin')->user() : Auth::user();
        $logoutRoute = $isCentralRequest
            ? 'central.logout'
            : (tenant() !== null ? 'tenant.logout' : 'logout');
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
                    <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                        <span class="text-slate-200">{{ __('Dashboard') }}</span>
                    </x-nav-link>

                    @if (tenant() !== null)
                        <x-nav-link :href="route('tenant.sports.index')" :active="request()->routeIs('tenant.sports.*')">
                            <span class="text-slate-200">Sports</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.venues.index')" :active="request()->routeIs('tenant.venues.*')">
                            <span class="text-slate-200">Venues</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.teams.index')" :active="request()->routeIs('tenant.teams.*')">
                            <span class="text-slate-200">Teams</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.players.index')" :active="request()->routeIs('tenant.players.*')">
                            <span class="text-slate-200">Players</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.games.index')" :active="request()->routeIs('tenant.games.*')">
                            <span class="text-slate-200">Schedules</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.audits.game-results.index')" :active="request()->routeIs('tenant.audits.game-results.*')">
                            <span class="text-slate-200">Result Audits</span>
                        </x-nav-link>

                        <x-nav-link :href="route('tenant.standings.index')" :active="request()->routeIs('tenant.standings.*')">
                            <span class="text-slate-200">Standings</span>
                        </x-nav-link>

                        @if (tenant()?->plan === 'pro')
                            <x-nav-link :href="route('tenant.pro.analytics')" :active="request()->routeIs('tenant.pro.analytics')">
                                <span class="text-slate-200">Analytics</span>
                            </x-nav-link>

                            <x-nav-link :href="route('tenant.pro.bracket')" :active="request()->routeIs('tenant.pro.bracket')">
                                <span class="text-slate-200">Bracket</span>
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-white/10 text-sm leading-4 font-medium rounded-md text-slate-200 bg-white/5 hover:bg-white/10 focus:outline-none transition ease-in-out duration-150">
                            <div class="text-slate-100">{{ $authenticatedUser?->name ?? 'Account' }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (! $isCentralRequest)
                            <x-dropdown-link :href="route('profile.edit')">
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
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 transition duration-150 ease-in-out">
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
        <div class="pt-2 pb-3 space-y-1 bg-slate-950/70">
            <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs('dashboard') || request()->routeIs('tenant.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (tenant() !== null)
                <x-responsive-nav-link :href="route('tenant.sports.index')" :active="request()->routeIs('tenant.sports.*')">
                    Sports
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.venues.index')" :active="request()->routeIs('tenant.venues.*')">
                    Venues
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.teams.index')" :active="request()->routeIs('tenant.teams.*')">
                    Teams
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.players.index')" :active="request()->routeIs('tenant.players.*')">
                    Players
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.games.index')" :active="request()->routeIs('tenant.games.*')">
                    Schedules
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.audits.game-results.index')" :active="request()->routeIs('tenant.audits.game-results.*')">
                    Result Audits
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('tenant.standings.index')" :active="request()->routeIs('tenant.standings.*')">
                    Standings
                </x-responsive-nav-link>

                @if (tenant()?->plan === 'pro')
                    <x-responsive-nav-link :href="route('tenant.pro.analytics')" :active="request()->routeIs('tenant.pro.analytics')">
                        Analytics
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('tenant.pro.bracket')" :active="request()->routeIs('tenant.pro.bracket')">
                        Bracket
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/10 bg-slate-950/70">
            <div class="px-4">
                <div class="font-medium text-base text-slate-100">{{ $authenticatedUser?->name ?? 'Account' }}</div>
                <div class="font-medium text-sm text-slate-400">{{ $authenticatedUser?->email ?? '' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                @if (! $isCentralRequest)
                    <x-responsive-nav-link :href="route('profile.edit')">
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
