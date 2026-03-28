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

        <section class="relative overflow-hidden rounded-[2rem] border border-blue-400/20 bg-slate-950/70 px-4 py-10 sm:px-6">
            <h2 class="pointer-events-none absolute left-1/2 top-2 -translate-x-1/2 select-none text-6xl font-black uppercase tracking-tight text-blue-500/35 sm:text-7xl md:text-8xl">Pricing</h2>

            <div class="relative mt-12 grid gap-5 lg:grid-cols-2">
                <article class="rounded-[1.75rem] border border-blue-400/35 bg-[radial-gradient(circle_at_30%_20%,rgba(59,130,246,0.32),rgba(15,23,42,0.88)_45%,rgba(2,6,23,0.95)_100%)] p-6 shadow-[0_0_24px_rgba(37,99,235,0.45)]">
                    <p class="text-sm font-medium tracking-wide text-slate-200">Basic</p>
                    <div class="mt-2 flex items-end gap-1">
                        <p class="text-4xl font-extrabold text-white">$19</p>
                        <p class="mb-1 text-sm text-slate-300">/month</p>
                    </div>
                    <p class="mt-2 text-sm text-slate-300">Complete manual operations for day-to-day intramurals.</p>

                    <ul class="mt-5 space-y-3 text-sm text-slate-100">
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Manage sports, venues, teams, and players</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Schedule games and submit results</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> View standings and result audit history</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Full operational use without advanced automation</li>
                    </ul>

                    <a href="#subscribe" class="mt-6 inline-flex w-full items-center justify-center rounded-full border border-white/20 bg-gradient-to-b from-slate-600/60 to-slate-900/80 px-4 py-2.5 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.35)] transition hover:from-slate-500/70 hover:to-slate-800/90">Start with Basic</a>
                </article>

                <article class="relative rounded-[1.75rem] border border-blue-400/70 bg-[radial-gradient(circle_at_50%_0%,rgba(59,130,246,0.5),rgba(30,41,59,0.9)_40%,rgba(2,6,23,0.98)_100%)] p-6 shadow-[0_0_36px_rgba(37,99,235,0.7)]">
                    <span class="absolute left-1/2 top-0 -translate-x-1/2 -translate-y-1/2 rounded-full border border-blue-200/50 bg-gradient-to-r from-blue-500 to-indigo-500 px-3 py-1 text-xs font-semibold text-white shadow-[0_0_20px_rgba(59,130,246,0.65)]">Popular</span>
                    <p class="text-sm font-medium tracking-wide text-blue-100">Pro</p>
                    <div class="mt-2 flex items-end gap-1">
                        <p class="text-4xl font-extrabold text-white">$49</p>
                        <p class="mb-1 text-sm text-blue-100">/month</p>
                    </div>
                    <p class="mt-2 text-sm text-blue-100/90">Automation and insights for faster, data-driven league management.</p>

                    <ul class="mt-5 space-y-3 text-sm text-white">
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Everything in Basic plus analytics dashboard</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Bracket generator with winner progression</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Bracket audit tracking</li>
                        <li class="flex items-center gap-2"><span class="text-blue-300">●</span> Standings and audit exports (CSV/PDF)</li>
                    </ul>

                    <a href="#subscribe" class="mt-6 inline-flex w-full items-center justify-center rounded-full border border-blue-300/40 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_0_22px_rgba(59,130,246,0.75)] transition hover:brightness-110">Upgrade to Pro</a>
                </article>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-slate-900/75 p-6">
            <h3 class="text-xl font-semibold text-white">Feature comparison</h3>
            <p class="mt-1 text-sm text-slate-300">Basic gives complete manual operations. Pro adds automation, analytics, and exports.</p>

            <div class="mt-4 overflow-hidden rounded-xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-slate-950/70 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Capability</th>
                            <th class="px-4 py-3 text-left font-medium">Basic</th>
                            <th class="px-4 py-3 text-left font-medium">Pro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-slate-100">
                        <tr>
                            <td class="px-4 py-3">Sports, venues, teams, players</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">Scheduling, results, standings</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">Result audit history</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">Analytics dashboard</td>
                            <td class="px-4 py-3 text-slate-400">Not included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">Bracket generator + progression</td>
                            <td class="px-4 py-3 text-slate-400">Not included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3">Bracket audits + CSV/PDF exports</td>
                            <td class="px-4 py-3 text-slate-400">Not included</td>
                            <td class="px-4 py-3 text-emerald-300">Included</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-slate-900/75 p-6">
            <h3 class="text-xl font-semibold text-white">Self-service upgrade flow</h3>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-200">
                <li>Start with Basic and run your intramurals normally.</li>
                <li>Request Pro upgrade from your tenant dashboard.</li>
                <li>Central admin reviews and approves the request.</li>
                <li>Pro features unlock automatically in your tenant workspace.</li>
            </ol>
            <p class="mt-3 text-xs text-slate-400">When a Basic user opens a Pro-only feature, the system shows an upgrade prompt instead of a dead-end block.</p>
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
                        <option value="basic" @selected(old('plan') === 'basic')>Basic</option>
                        <option value="pro" @selected(old('plan') === 'pro')>Pro</option>
                    </select>
                    @error('plan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
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
