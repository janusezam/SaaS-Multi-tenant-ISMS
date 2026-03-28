@php
    $recaptchaEnabled = filled(config('services.recaptcha.site_key'));
    $recaptchaVersion = (string) config('services.recaptcha.version', 'v3');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-static-auth>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Central Login | ISMS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_10%_12%,rgba(14,165,233,0.25),transparent_35%),radial-gradient(circle_at_86%_0%,rgba(249,115,22,0.2),transparent_30%),linear-gradient(180deg,#020617,#0f172a)] px-4 py-8 sm:px-6">
            <div class="mx-auto grid w-full max-w-6xl gap-6 lg:grid-cols-2">
                <section class="rounded-3xl border border-cyan-300/20 bg-slate-950/60 p-6 shadow-2xl shadow-cyan-950/40 backdrop-blur sm:p-8">
                    <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-3 text-cyan-100">
                        <img src="{{ asset('images/isms-logo.png') }}" alt="ISMS logo" class="h-10 w-auto">
                        <span class="text-xs uppercase tracking-[0.24em]">Central Control</span>
                    </a>

                    <div class="mt-8 space-y-4">
                        <p class="inline-flex rounded-full border border-cyan-300/30 bg-cyan-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-cyan-100">SaaS Intramurals Command Center</p>
                        <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl">Super Admin Access</h1>
                        <p class="max-w-lg text-sm leading-7 text-slate-300">Monitor schools, subscriptions, plan upgrades, and platform health from one ISMS cockpit built for multi-tenant university operations.</p>
                    </div>

                    <ul class="mt-8 space-y-3 text-sm text-slate-200">
                        <li class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">Tenant onboarding and domain provisioning</li>
                        <li class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">Subscription control with plan-level enforcement</li>
                        <li class="rounded-xl border border-white/10 bg-white/5 px-4 py-3">Centralized lock/unlock and lifecycle governance</li>
                    </ul>
                </section>

                <section class="rounded-3xl border border-white/15 bg-[#0f172a]/95 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-8">
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-200/80">ISMS Central Console</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Secure Sign In</h2>
                    <p class="mt-2 text-sm text-white/70">Use your super admin credentials to continue.</p>

                    <x-auth-session-status class="mt-5" :status="session('status')" />

                    <form method="POST" action="{{ route('central.login.store') }}" id="central-login-form" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-white/80">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@isms.test" class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/40">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-white/80">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/40">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label for="remember" class="inline-flex items-center">
                            <input id="remember" type="checkbox" name="remember" class="rounded border-white/25 bg-[#0b1224] text-cyan-500 shadow-sm focus:ring-cyan-500">
                            <span class="ms-2 text-sm text-white/75">Remember me</span>
                        </label>

                        @if ($recaptchaEnabled && $recaptchaVersion === 'v2')
                            <div class="pt-1">
                                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                                <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
                            </div>
                        @elseif ($recaptchaEnabled)
                            <input type="hidden" name="recaptcha_token" id="central-recaptcha-token" value="">
                            <x-input-error :messages="$errors->get('recaptcha_token')" class="mt-2" />
                        @endif

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-700/30 transition hover:bg-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">Sign in to Central App</button>
                    </form>
                </section>
            </div>
        </div>

        @if ($recaptchaEnabled && $recaptchaVersion === 'v2')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @elseif ($recaptchaEnabled)
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
            <script>
                (function () {
                    var form = document.getElementById('central-login-form');
                    var tokenInput = document.getElementById('central-recaptcha-token');
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
                            grecaptcha.execute(siteKey, { action: 'central_login' }).then(function (token) {
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
