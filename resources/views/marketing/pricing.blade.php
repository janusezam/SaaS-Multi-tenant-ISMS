<!DOCTYPE html>
<html lang="en" data-theme="dark" data-theme-static-auth>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ISMS Pricing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_10%_0%,rgba(14,165,233,0.2),transparent_35%),radial-gradient(circle_at_90%_20%,rgba(245,158,11,0.18),transparent_40%),linear-gradient(180deg,#020617,#111827)]"></div>

    <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
        <a href="{{ route('public.landing') }}" class="text-lg font-semibold tracking-wide text-cyan-200">ISMS SaaS</a>
        <a href="{{ route('public.landing') }}" class="rounded-lg border border-white/20 px-4 py-2 text-sm hover:bg-white/10">Back to landing</a>
    </header>

    <main class="mx-auto w-full max-w-6xl space-y-8 px-6 pb-16 pt-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="relative overflow-hidden rounded-[2rem] border border-blue-500/30 bg-[#070b16]/80 p-6 sm:p-8">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(37,99,235,0.22),transparent_45%),radial-gradient(circle_at_80%_10%,rgba(29,78,216,0.2),transparent_42%)]"></div>
            <h2 class="pointer-events-none absolute left-1/2 top-4 -translate-x-1/2 select-none text-6xl font-black uppercase tracking-tight text-blue-500/45 sm:text-7xl md:text-8xl">Pricing</h2>

            <div class="relative mt-16">
                <p class="text-center text-sm text-slate-300">Plans and pricing are centrally managed by ISMS Business Control.</p>

                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($plans as $plan)
                        @php
                            $isPro = strtolower((string) $plan->code) === 'pro';
                            $featureList = $isPro
                                ? ['Analytics dashboard', 'Bracket automation', 'CSV and PDF exports']
                                : ['Sports and team operations', 'Schedules and results', 'Standings and audit trails'];
                        @endphp

                        <article class="relative rounded-[1.75rem] border p-6 {{ $isPro ? 'border-blue-400/70 bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.45),rgba(30,41,59,0.9)_40%,rgba(2,6,23,0.98)_100%)] shadow-[0_0_30px_rgba(37,99,235,0.65)]' : 'border-blue-400/35 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.28),rgba(15,23,42,0.88)_45%,rgba(2,6,23,0.95)_100%)] shadow-[0_0_20px_rgba(37,99,235,0.35)]' }}">
                            @if ($isPro)
                                <span class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/2 rounded-full border border-blue-200/50 bg-gradient-to-r from-blue-500 to-indigo-500 px-3 py-1 text-xs font-semibold text-white shadow-[0_0_16px_rgba(59,130,246,0.7)]">Popular</span>
                            @endif

                            <p class="text-2xl font-semibold text-white">{{ $plan->name }}</p>
                            <div class="mt-2 flex items-end gap-1">
                                <p class="text-5xl font-extrabold text-white leading-none">${{ number_format((float) $plan->monthly_price, 0) }}</p>
                                <p class="mb-1 text-xl text-slate-300">/month</p>
                            </div>

                            <p class="mt-2 text-sm text-slate-200">
                                ${{ number_format((float) $plan->yearly_price, 2) }} /year
                                @if ((float) $plan->yearly_discount_percent > 0)
                                    <span class="text-emerald-300">(save {{ number_format((float) $plan->yearly_discount_percent, 2) }}%)</span>
                                @endif
                            </p>

                            <ul class="mt-5 space-y-2.5 text-sm text-slate-100">
                                @foreach ($featureList as $feature)
                                    <li class="flex items-center gap-2">
                                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/80 text-[11px] font-bold text-white">✓</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="#subscribe" class="mt-6 inline-flex w-full items-center justify-center rounded-full border px-4 py-2.5 text-sm font-semibold transition {{ $isPro ? 'border-blue-300/40 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600 text-white shadow-[0_0_20px_rgba(59,130,246,0.75)] hover:brightness-110' : 'border-white/20 bg-gradient-to-b from-slate-600/70 to-slate-900/90 text-white hover:from-slate-500/80 hover:to-slate-800' }}">
                                {{ $isPro ? 'Upgrade Now' : 'Start Plan' }}
                            </a>
                        </article>
                    @empty
                        <p class="text-sm text-slate-300">No active plans are currently available.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="subscribe" class="rounded-2xl border border-white/10 bg-slate-900/75 p-6">
            <h3 class="text-xl font-semibold text-white">Start your school subscription</h3>
            <p class="mt-1 text-sm text-slate-300">New schools are created with pending status and activated after central admin approval.</p>

            <form action="{{ route('public.subscribe') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm text-slate-300" for="name">School Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                    @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm text-slate-300" for="school_address">School Address</label>
                    <input id="school_address" name="school_address" value="{{ old('school_address') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                    @error('school_address')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="tenant_admin_name">Admin Name</label>
                    <input id="tenant_admin_name" name="tenant_admin_name" value="{{ old('tenant_admin_name') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                    @error('tenant_admin_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="tenant_admin_email">Admin Email</label>
                    <input id="tenant_admin_email" type="email" name="tenant_admin_email" value="{{ old('tenant_admin_email') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                    @error('tenant_admin_email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="subdomain">Subdomain</label>
                    <input id="subdomain" name="subdomain" value="{{ old('subdomain') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                    @error('subdomain')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="plan">Plan</label>
                    <select id="plan" name="plan" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->code }}" @selected(old('plan', 'basic') === $plan->code)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('plan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="billing_cycle">Billing Cycle</label>
                    <select id="billing_cycle" name="billing_cycle" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100" required>
                        <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly</option>
                    </select>
                    @error('billing_cycle')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="coupon_code">Promo Code (optional)</label>
                    <input id="coupon_code" name="coupon_code" value="{{ old('coupon_code') }}" class="w-full rounded-xl border border-white/10 bg-slate-950/70 text-slate-100">
                    @error('coupon_code')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-5 py-3 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">
                        Submit Subscription Request
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
