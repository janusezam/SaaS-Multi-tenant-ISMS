<!DOCTYPE html>
<html lang="en">
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

        <section class="grid gap-5 md:grid-cols-2">
            <article class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Basic</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Core intramurals operations</h2>
                <ul class="mt-4 space-y-2 text-sm text-slate-300">
                    <li>Sports, venues, teams, players, fixtures, and standings</li>
                    <li>Role-based access for school operations</li>
                    <li>No advanced analytics or bracket automation</li>
                </ul>
            </article>

            <article class="rounded-2xl border border-amber-300/30 bg-amber-500/10 p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-amber-200">Pro</p>
                <h2 class="mt-2 text-2xl font-semibold text-white">Advanced controls and insights</h2>
                <ul class="mt-4 space-y-2 text-sm text-amber-100">
                    <li>Everything in Basic, plus analytics dashboards</li>
                    <li>Bracket automation and bracket audit trail</li>
                    <li>CSV/PDF reporting and exports</li>
                </ul>
            </article>
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
