<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upgrade Requested</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-2xl items-center px-6">
        <div class="w-full rounded-2xl border border-emerald-300/30 bg-emerald-500/10 p-7">
            <p class="text-xs uppercase tracking-[0.2em] text-emerald-200">Upgrade request received</p>
            <h1 class="mt-2 text-2xl font-semibold text-white">Your request to upgrade to Pro was submitted.</h1>
            <p class="mt-3 text-sm text-emerald-100">A central administrator will review and approve the plan change. Once approved, access updates automatically in your tenant environment.</p>
            <a href="{{ route('public.landing') }}" class="mt-5 inline-block rounded-lg border border-white/20 px-4 py-2 text-sm hover:bg-white/10">Back to ISMS</a>
        </div>
    </div>
</body>
</html>
