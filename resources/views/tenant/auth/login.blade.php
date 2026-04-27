@php
    $recaptchaEnabled = filled(config('services.recaptcha.site_key'));
    $recaptchaVersion = (string) config('services.recaptcha.version', 'v3');
    $googleAuthEnabled = (bool) config('services.google.enabled', false);
    $tenantSetting = tenant() !== null
        ? \App\Models\TenantSetting::query()->firstWhere('tenant_id', (string) tenant('id'))
        : null;

    $brandBadge = trim((string) ($tenantSetting?->login_brand_badge ?? ''));
    $brandHeading = trim((string) ($tenantSetting?->login_brand_heading ?? ''));
    $brandDescription = trim((string) ($tenantSetting?->login_brand_description ?? ''));
    $brandFeature1 = trim((string) ($tenantSetting?->login_brand_feature_1 ?? ''));
    $brandFeature2 = trim((string) ($tenantSetting?->login_brand_feature_2 ?? ''));
    $brandFeature3 = trim((string) ($tenantSetting?->login_brand_feature_3 ?? ''));

    $loginBranding = [
        'badge' => $brandBadge !== '' ? $brandBadge : 'Your School Operations Hub',
        'heading' => $brandHeading !== '' ? $brandHeading : 'Sign in to your intramurals workspace',
        'description' => $brandDescription !== '' ? $brandDescription : 'Access events, teams, fixtures, game results, and standings in one SaaS platform built for university sports programs.',
        'features' => [
            $brandFeature1 !== '' ? $brandFeature1 : 'Role-based access for admins, facilitators, and staff',
            $brandFeature2 !== '' ? $brandFeature2 : 'Real-time scheduling and score tracking',
            $brandFeature3 !== '' ? $brandFeature3 : 'Plan-gated analytics, brackets, and exports',
        ],
    ];

    $loginLogoUrl = asset('images/isms-logo.png');
    $logoPath = trim((string) ($tenantSetting?->branding_logo_path ?? ''));

    if ($logoPath !== '') {
        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            $loginLogoUrl = $logoPath;
        } else {
            $normalizedPath = ltrim(str_replace('\\', '/', $logoPath), '/');
            $normalizedPath = preg_replace('#^(public/)+#', '', $normalizedPath) ?? $normalizedPath;
            $normalizedPath = preg_replace('#^(storage/)+#', '', $normalizedPath) ?? $normalizedPath;
            $loginLogoUrl = tenant_asset($normalizedPath);
        }
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-static-auth>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ $loginLogoUrl }}">
        <title>Tenant Login | ISMS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_12%_8%,rgba(16,185,129,0.22),transparent_35%),radial-gradient(circle_at_88%_0%,rgba(14,165,233,0.18),transparent_30%),linear-gradient(180deg,#03111f,#0b1224)] px-4 py-8 sm:px-6">
            <div class="mx-auto grid w-full max-w-6xl gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-emerald-300/20 bg-slate-950/60 p-6 shadow-2xl shadow-emerald-950/30 backdrop-blur sm:p-8">
                    <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-3 text-emerald-100">
                        <img src="{{ $loginLogoUrl }}" alt="ISMS logo" class="h-16 w-auto">
                        <span class="text-xs uppercase tracking-[0.24em]">Tenant Workspace</span>
                    </a>

                    <div class="mt-8 space-y-4">
                        <p class="inline-flex rounded-full border border-emerald-300/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-emerald-100">{{ $loginBranding['badge'] }}</p>
                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl">{{ $loginBranding['heading'] }}</h1>
                        <p class="max-w-lg text-sm leading-7 text-slate-300">{{ $loginBranding['description'] }}</p>
                    </div>

                    <ul class="mt-8 space-y-3 text-sm text-slate-200">
                        @foreach ($loginBranding['features'] as $feature)
                            <li class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">{{ $feature }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="rounded-3xl border border-white/15 bg-[#0f172a]/95 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-8">
                    <p class="text-xs uppercase tracking-[0.2em] text-emerald-200/80">School Workspace</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Welcome Back</h2>
                    <p class="mt-2 text-sm text-white/70">Sign in with your assigned school account.</p>

                    <x-auth-session-status class="mt-5" :status="session('status')" />

                    <form method="POST" action="{{ route('tenant.login.store') }}" id="tenant-login-form" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-white/80">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@school.edu" class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-white/80">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label for="remember" class="inline-flex items-center">
                            <input id="remember" type="checkbox" name="remember" class="rounded border-white/25 bg-[#0b1224] text-emerald-500 shadow-sm focus:ring-emerald-500">
                            <span class="ms-2 text-sm text-white/75">Remember me</span>
                        </label>

                        @if ($recaptchaEnabled && $recaptchaVersion === 'v2')
                            <div class="pt-1">
                                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
                            </div>
                        @elseif ($recaptchaEnabled)
                            <input type="hidden" name="recaptcha_token" id="tenant-recaptcha-token" value="">
                            <x-input-error :messages="$errors->get('recaptcha_token')" class="mt-2" />
                        @endif

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/30 transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Sign in to Tenant App</button>
                    </form>

                    @if ($googleAuthEnabled)
                        <div class="mt-4">
                            <a href="{{ route('tenant.login.google.redirect') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24">
                                    <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.5 3.8-5.5 3.8-3.3 0-6-2.8-6-6.2s2.7-6.2 6-6.2c1.9 0 3.2.8 4 1.5l2.7-2.6C17.1 2.9 14.8 2 12 2 6.9 2 2.8 6.3 2.8 11.5S6.9 21 12 21c6.9 0 9.2-4.9 9.2-7.5 0-.5-.1-.9-.1-1.3H12Z"/>
                                </svg>
                                Sign in with Google
                            </a>
                        </div>
                    @endif

                    <div class="mt-5 flex items-center justify-between text-sm">
                        <a href="{{ route('tenant.password.otp.request') }}" class="text-emerald-200 transition hover:text-emerald-100">Forgot password?</a>
                        <a href="{{ route('tenant.register') }}" class="text-emerald-200 transition hover:text-emerald-100">Create tenant account request</a>
                    </div>
                </section>
            </div>
        </div>

        @if ($recaptchaEnabled && $recaptchaVersion === 'v2')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @elseif ($recaptchaEnabled)
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
            <script>
                (function () {
                    var form = document.getElementById('tenant-login-form');
                    var tokenInput = document.getElementById('tenant-recaptcha-token');
                    var siteKey = @json(config('services.recaptcha.site_key'));

                    if (!form || !tokenInput || !siteKey) {
                        return;
                    }

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        if (typeof grecaptcha === 'undefined') {
                            form.submit();

                            return;
                        }

                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: 'tenant_login' }).then(function (token) {
                                tokenInput.value = token;
                                form.submit();
                            });
                        });
                    });
                })();
            </script>
        @endif
    </body>
</html>
