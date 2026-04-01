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
        <div class="flex min-h-screen items-center justify-center bg-[linear-gradient(180deg,#070b12,#0b1220)] px-4 py-8 sm:px-6">
            <section class="w-full max-w-md rounded-lg border border-slate-700 bg-[#0b1220] p-6 shadow-2xl shadow-black/40 sm:p-7">
                <div class="mb-6 flex items-center justify-between">
                    <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-2 text-slate-300">
                        <img src="{{ asset('images/isms-logo.png') }}" alt="ISMS logo" class="h-8 w-auto">
                        <span class="text-[11px] uppercase tracking-[0.18em]">Central</span>
                    </a>
                    <span class="rounded-md border border-slate-600 px-2 py-1 text-[10px] uppercase tracking-[0.16em] text-slate-400">Restricted</span>
                </div>

                <h1 class="text-xl font-semibold text-slate-100">Super Admin Login</h1>
                <p class="mt-1 text-xs text-slate-400">Authorized access only</p>

                <x-auth-session-status class="mt-4" :status="session('status')" />

                <form method="POST" action="{{ route('central.login.store') }}" id="central-login-form" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-200">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1 block w-full rounded-md border border-slate-600 bg-[#080d18] px-3 py-2.5 text-slate-100 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-500/40">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-200">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1 block w-full rounded-md border border-slate-600 bg-[#080d18] px-3 py-2.5 text-slate-100 shadow-sm outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-500/40">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label for="remember" class="inline-flex items-center">
                            <input id="remember" type="checkbox" name="remember" class="rounded border-slate-500 bg-[#080d18] text-slate-300 shadow-sm focus:ring-slate-500">
                            <span class="ms-2 text-sm text-slate-300">Remember me</span>
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

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-slate-500 bg-slate-800 px-4 py-2.5 text-sm font-medium text-slate-100 transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-0">Login</button>
                </form>
            </section>
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
