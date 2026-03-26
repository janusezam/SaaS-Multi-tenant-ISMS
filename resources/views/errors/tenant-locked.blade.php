<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>School Account Locked</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        @php
            $status = (string) ($tenantStatus ?? 'inactive');
            $statusLabel = str_replace('_', ' ', strtoupper($status));
            $statusClasses = match ($status) {
                'active' => 'bg-emerald-500/20 text-emerald-200 border-emerald-300/30',
                'expired' => 'bg-rose-500/20 text-rose-200 border-rose-300/30',
                'suspended' => 'bg-amber-500/20 text-amber-200 border-amber-300/30',
                default => 'bg-slate-500/20 text-slate-200 border-slate-300/30',
            };
        @endphp

        <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div class="overflow-hidden rounded-2xl border border-amber-300/30 bg-slate-900/70 shadow-2xl shadow-slate-950/40 backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5 sm:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-200/90">Subscription Locked</p>
                        <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Your school account is temporarily locked</h1>
                    </div>

                    <div class="space-y-4 px-6 py-6 sm:px-8 sm:py-8">
                        <div class="grid gap-3 rounded-xl border border-white/10 bg-slate-950/40 p-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-400">School</p>
                                <p class="mt-1 text-sm font-semibold text-white">{{ $tenantName ?? 'Your school' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-400">Current Status</p>
                                <span class="mt-1 inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                            </div>
                            @if (! empty($tenantDueDate))
                                <div class="sm:col-span-2">
                                    <p class="text-xs uppercase tracking-wider text-slate-400">Subscription Due Date</p>
                                    <p class="mt-1 text-sm font-semibold text-white">{{ $tenantDueDate->format('F j, Y') }}</p>
                                </div>
                            @endif
                        </div>

                        <p class="text-sm leading-7 text-slate-200 sm:text-base">{{ $message ?? 'School subscription is not active yet. Please contact your school administrator.' }}</p>

                        @if ($status === 'suspended')
                            <p class="text-sm leading-7 text-slate-300 sm:text-base">Your school account is currently suspended. Contact your school administrator first, then request reactivation from the central ISMS team.</p>
                        @elseif ($status === 'expired')
                            <p class="text-sm leading-7 text-slate-300 sm:text-base">Your school subscription has expired. Ask your school administrator to submit a renewal request to the central ISMS team.</p>
                        @else
                            <p class="text-sm leading-7 text-slate-300 sm:text-base">If you are a school admin, you can request reactivation or plan renewal from the central ISMS team.</p>
                        @endif

                        <div class="flex flex-wrap gap-3 pt-2">
                            <a href="{{ route('tenant.login') }}" class="inline-flex items-center rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                                Back to Login
                            </a>
                            <a href="{{ route('central.login') }}" class="inline-flex items-center rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-amber-300">
                                Contact Central Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
