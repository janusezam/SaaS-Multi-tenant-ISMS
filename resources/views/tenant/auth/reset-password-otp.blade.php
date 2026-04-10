<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-static-auth>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">
        <title>Reset Password | ISMS Tenant</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_12%_8%,rgba(16,185,129,0.22),transparent_35%),radial-gradient(circle_at_88%_0%,rgba(14,165,233,0.18),transparent_30%),linear-gradient(180deg,#03111f,#0b1224)] px-4 py-8 sm:px-6">
            <div class="mx-auto max-w-xl rounded-3xl border border-white/15 bg-[#0f172a]/95 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-8">
                <h1 class="text-2xl font-semibold text-white">Reset Password</h1>
                <p class="mt-2 text-sm text-white/70">Enter the OTP sent to your email and set a new password.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('tenant.password.otp.reset') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-white/80">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="otp" class="block text-sm font-medium text-white/80">OTP Code</label>
                        <input id="otp" name="otp" type="text" inputmode="numeric" maxlength="6" value="{{ old('otp') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                        <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-white/80">New Password</label>
                        <input id="password" name="password" type="password" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-white/80">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white placeholder:text-white/45 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/40">
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/30 transition hover:bg-emerald-500">Reset Password</button>
                </form>

                <div class="mt-5 text-sm">
                    <a href="{{ route('tenant.login') }}" class="text-emerald-200 transition hover:text-emerald-100">Back to tenant login</a>
                </div>
            </div>
        </div>
    </body>
</html>
