@php
    $superAdmin = Auth::guard('super_admin')->user();
@endphp

<aside class="isms-sidebar hidden border-r md:sticky md:top-0 md:flex md:h-screen md:w-72 md:shrink-0 md:flex-col md:self-start md:overflow-y-auto">
    <div class="flex h-16 items-center gap-3 border-b px-5" style="border-color: var(--isms-stroke);">
        <a href="{{ route('central.universities.index') }}" class="inline-flex items-center gap-2">
            <x-application-logo class="block h-8 w-auto fill-current text-cyan-300" />
            <span class="text-sm font-semibold tracking-wide isms-text">ISMS Central</span>
        </a>
    </div>

    <div class="px-4 py-4 text-xs uppercase tracking-[0.2em] isms-text-muted">
        Central Navigation
    </div>

    <nav class="flex-1 space-y-1 px-3 pb-4 text-sm">
        <a href="{{ route('central.universities.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('central.universities.*') ? 'border border-cyan-300/40 bg-cyan-500/10 text-cyan-900 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100' : 'border border-transparent isms-sidebar-link hover:bg-slate-200/60 dark:hover:bg-white/5' }}">School Management</a>

        <a href="{{ route('central.business-control.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('central.business-control.*') ? 'border border-cyan-300/40 bg-cyan-500/10 text-cyan-900 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100' : 'border border-transparent isms-sidebar-link hover:bg-slate-200/60 dark:hover:bg-white/5' }}">Business Control</a>

        <a href="{{ route('central.business-control.support-updates.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('central.business-control.support-updates.*') ? 'border border-cyan-300/40 bg-cyan-500/10 text-cyan-900 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100' : 'border border-transparent isms-sidebar-link hover:bg-slate-200/60 dark:hover:bg-white/5' }}">Support &amp; Updates</a>

        <a href="{{ route('central.tenant-monitoring.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('central.tenant-monitoring.*') ? 'border border-cyan-300/40 bg-cyan-500/10 text-cyan-900 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100' : 'border border-transparent isms-sidebar-link hover:bg-slate-200/60 dark:hover:bg-white/5' }}">Tenant Monitoring</a>

        <a href="{{ route('central.subscription-notification-logs.index') }}" class="block rounded-lg px-3 py-2 {{ request()->routeIs('central.subscription-notification-logs.*') ? 'border border-cyan-300/40 bg-cyan-500/10 text-cyan-900 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100' : 'border border-transparent isms-sidebar-link hover:bg-slate-200/60 dark:hover:bg-white/5' }}">Notification Logs</a>

    </nav>

    <div class="border-t px-4 py-4" style="border-color: var(--isms-stroke);">
        <p class="mb-3 text-sm isms-text">{{ $superAdmin?->name ?? 'Super Admin' }}</p>

        <div class="space-y-2">
            <button type="button" data-theme-toggle class="isms-theme-toggle w-full">
                <span data-theme-label>Light mode</span>
            </button>

            <form method="POST" action="{{ route('central.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-rose-300/40 bg-rose-500/10 px-3 py-2 text-left text-sm text-rose-900 hover:bg-rose-500/15 dark:border-rose-300/30 dark:bg-rose-500/20 dark:text-rose-100 dark:hover:bg-rose-500/30">Log Out</button>
            </form>
        </div>
    </div>
</aside>
