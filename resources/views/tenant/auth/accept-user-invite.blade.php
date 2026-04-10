<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-static-auth>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">
        <title>Activate Tenant Account | ISMS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_12%_8%,rgba(16,185,129,0.22),transparent_35%),radial-gradient(circle_at_88%_0%,rgba(14,165,233,0.18),transparent_30%),linear-gradient(180deg,#03111f,#0b1224)] px-4 py-8 sm:px-6">
            <div class="mx-auto max-w-xl rounded-3xl border border-white/15 bg-[#0f172a]/95 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-8">
                <h1 class="text-2xl font-semibold text-white">Activate Tenant Account</h1>
                <p class="mt-2 text-sm text-white/70">Your account request was approved. Activate your account to access your tenant workspace.</p>

                <form method="POST" action="{{ route('tenant.user-invite.activate') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <p class="text-sm text-white/80">Email</p>
                        <p class="mt-1 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white">{{ $email }}</p>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/30 transition hover:bg-emerald-500">Activate Account</button>
                </form>
            </div>
        </div>
    </body>
</html>
