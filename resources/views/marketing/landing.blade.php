<!DOCTYPE html>
<html lang="en" data-theme="dark" data-theme-static-auth>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ISMS | Intramurals SaaS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_15%_20%,rgba(14,165,233,0.2),transparent_40%),radial-gradient(circle_at_85%_10%,rgba(249,115,22,0.2),transparent_35%),linear-gradient(180deg,#020617,#0f172a)]"></div>

    <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6">
        <a href="{{ route('public.landing') }}" class="inline-flex items-center" aria-label="ISMS SaaS home">
            <img src="{{ asset('images/isms-logo.png') }}" class="h-28 w-auto sm:h-32 transition-transform hover:scale-105" alt="ISMS logo">
        </a>
        <nav class="flex items-center gap-4 text-sm">
            <a href="{{ route('central.login') }}" class="rounded-lg border border-white/20 px-4 py-2 hover:bg-white/10">Admin Login</a>
        </nav>
    </header>

    <main class="mx-auto grid w-full max-w-6xl gap-8 px-6 pb-16 pt-6 lg:grid-cols-2 lg:pt-16">
        <section class="space-y-6">
            <p class="inline-block rounded-full border border-cyan-300/30 bg-cyan-500/10 px-3 py-1 text-xs uppercase tracking-[0.18em] text-cyan-100">Multi-tenant intramurals</p>
            <h1 class="text-4xl font-semibold leading-tight text-white sm:text-5xl">Run every school league from one platform.</h1>
            <p class="max-w-xl text-base text-slate-300">ISMS gives each school a dedicated workspace to run intramurals end-to-end on Basic, then upgrade to Pro for analytics, bracket automation, and report exports.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('public.pricing') }}" class="rounded-xl border border-amber-300/50 bg-amber-500/20 px-5 py-3 text-sm font-medium text-amber-100 hover:bg-amber-500/30">Compare Basic vs Pro</a>
                <a href="{{ route('public.pricing') }}#subscribe" class="rounded-xl border border-white/20 px-5 py-3 text-sm font-medium text-white hover:bg-white/10">Start with Basic</a>
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <h2 class="text-lg font-semibold text-white">Plan snapshot</h2>
            <ul class="mt-4 grid gap-3 text-sm text-slate-200">
                <li class="rounded-xl border border-white/10 bg-slate-900/60 px-4 py-3"><span class="font-semibold text-cyan-100">Basic:</span> Sports, venues, teams, players, schedules, results, standings, and result audit history.</li>
                <li class="rounded-xl border border-white/10 bg-slate-900/60 px-4 py-3"><span class="font-semibold text-emerald-100">Pro:</span> Everything in Basic plus analytics, bracket generation, bracket audits, and CSV/PDF exports.</li>
                <li class="rounded-xl border border-white/10 bg-slate-900/60 px-4 py-3">Subscription plans are managed centrally for each school workspace.</li>
                <li class="rounded-xl border border-white/10 bg-slate-900/60 px-4 py-3">Dedicated school subdomain with centralized subscription management and tenant-level enforcement.</li>
            </ul>
        </section>
    </main>
</body>
</html>
