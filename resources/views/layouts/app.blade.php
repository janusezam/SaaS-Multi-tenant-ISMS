<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $mediaUrl = static function (?string $path): ?string {
                if ($path === null || trim($path) === '') {
                    return null;
                }

                $normalized = str_replace('\\', '/', trim($path));

                if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                    return $normalized;
                }

                $normalized = ltrim($normalized, '/');
                $normalized = preg_replace('#^(public/)+#', '', $normalized) ?? $normalized;
                $normalized = preg_replace('#^(storage/)+#', '', $normalized) ?? $normalized;

                if (tenant() !== null) {
                    return tenant_asset($normalized);
                }

                return asset('storage/'.$normalized);
            };

            $tenantSettings = null;
            $tenantThemePreference = 'system';
            $tenantPrimaryColor = '#06b6d4';
            $tenantSecondaryColor = '#6366f1';

            if (tenant() !== null) {
                $tenantSettings = \App\Models\TenantSetting::query()->firstWhere('tenant_id', tenant('id'));
                $tenantThemePreference = (string) ($tenantSettings?->theme_preference ?? 'system');
                $tenantPrimaryColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($tenantSettings?->brand_primary_color ?? '')) === 1
                    ? (string) $tenantSettings?->brand_primary_color
                    : '#06b6d4';
                $tenantSecondaryColor = preg_match('/^#[0-9A-Fa-f]{6}$/', (string) ($tenantSettings?->brand_secondary_color ?? '')) === 1
                    ? (string) $tenantSettings?->brand_secondary_color
                    : '#6366f1';
            }
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">

        <title>{{ tenant() !== null ? config('app.name', 'ISMS').' Tenant' : (request()->routeIs('central.*') ? config('app.name', 'ISMS').' Central' : config('app.name', 'ISMS')) }}</title>

        <script>
            (function () {
                var storageKey = 'isms-theme';
                var savedTheme = null;
                var tenantThemePreference = @json($tenantThemePreference);

                try {
                    savedTheme = localStorage.getItem(storageKey);
                } catch (e) {
                    savedTheme = null;
                }

                var fallbackTheme = tenantThemePreference === 'light' || tenantThemePreference === 'dark'
                    ? tenantThemePreference
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                var resolvedTheme = savedTheme || fallbackTheme;
                document.documentElement.setAttribute('data-theme', resolvedTheme);
            })();
        </script>

        @if (tenant() !== null)
            <style>
                :root {
                    --isms-glow-a: {{ $tenantPrimaryColor }}3d;
                    --isms-glow-b: {{ $tenantSecondaryColor }}2e;
                }
            </style>
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        @if (tenant() !== null)
            @if (request()->routeIs('tenant.subscription.*'))
                <div class="isms-shell min-h-screen">
                    <header class="isms-header relative z-10 border-b shadow-lg shadow-slate-950/20">
                        <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                            <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-sm text-slate-200 transition hover:bg-white/10">
                                <span aria-hidden="true">&larr;</span>
                                <span>Back to App</span>
                            </a>

                            <button type="button" data-theme-toggle class="isms-theme-toggle">
                                <span data-theme-label>Light mode</span>
                            </button>
                        </div>
                    </header>

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            @else
                <div class="md:hidden">
                    @include('layouts.navigation')
                </div>

                <div class="isms-shell min-h-screen md:flex">
                    @include('layouts.tenant-sidebar')

                    <div class="flex-1 min-w-0">
                        @isset($header)
                            <header class="isms-header relative z-10 border-b shadow-lg shadow-slate-950/20">
                                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    @php
                                        $tenantUser = Auth::user();
                                        $tenantUserInitial = strtoupper(substr((string) ($tenantUser?->name ?? 'A'), 0, 1));
                                    @endphp

                                    <div class="grid grid-cols-1 items-start gap-3 sm:grid-cols-3">
                                        <div class="min-w-0 sm:self-start">
                                            {{ $header }}
                                        </div>

                                        <div class="flex justify-start sm:justify-center sm:self-center">
                                            <a href="{{ route('tenant.subscription.show') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm transition {{ request()->routeIs('tenant.subscription.*') ? 'border-cyan-300/40 bg-cyan-500/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}">
                                                Subscription
                                            </a>
                                        </div>

                                        <div class="flex items-center justify-start gap-2 sm:justify-end sm:self-start">
                                            <a href="{{ route('tenant.settings.edit') }}" class="inline-flex items-center rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 transition hover:bg-white/10">
                                                Settings
                                            </a>

                                            <a href="{{ route('tenant.profile.edit') }}" class="inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border border-cyan-300/35 bg-cyan-500/20 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-500/30" title="Profile">
                                                @php
                                                    $headerProfileUrl = $mediaUrl($tenantUser?->profile_photo_path);
                                                @endphp
                                                @if ($headerProfileUrl !== null)
                                                    <img src="{{ $headerProfileUrl }}" alt="{{ $tenantUser?->name }}" class="h-full w-full object-cover">
                                                @else
                                                    {{ $tenantUserInitial }}
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </header>
                        @endisset

                        @if (session('upgrade_notice'))
                            <div class="max-w-7xl mx-auto px-4 pt-4 sm:px-6 lg:px-8">
                                <div class="rounded-xl border border-amber-300/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                                    {{ session('upgrade_notice') }}
                                </div>
                            </div>
                        @endif

                        <main>
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @endif

            @include('tenant.subscription.partials.upgrade-modal')
        @else
            @if (request()->routeIs('central.*'))
                <div class="md:hidden">
                    @include('layouts.navigation')
                </div>

                <div class="isms-shell min-h-screen md:flex">
                    @include('layouts.central-sidebar')

                    <div class="flex-1 min-w-0">
                        @isset($header)
                            <header class="isms-header relative z-10 border-b shadow-lg shadow-slate-950/20">
                                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            {{ $header }}
                                        </div>
                                    </div>
                                </div>
                            </header>
                        @endisset

                        <main>
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @else
                <div class="isms-shell min-h-screen">
                    @include('layouts.navigation')

                    <!-- Page Heading -->
                    @isset($header)
                        <header class="isms-header relative z-10 border-b shadow-lg shadow-slate-950/20">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            @endif
        @endif
    </body>
</html>
