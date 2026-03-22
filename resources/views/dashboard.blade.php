<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-cyan-300/80">Intramural Sports SaaS</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-100">Operations Command Center</h2>
            </div>
            <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                School Status: Active
            </div>
        </div>
    </x-slot>

    <div class="relative overflow-hidden py-8">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(34,211,238,0.16),transparent_40%),radial-gradient(circle_at_90%_0%,rgba(99,102,241,0.2),transparent_38%)]"></div>

        <div class="relative mx-auto grid w-full max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-12 lg:px-8">
            <aside class="rounded-3xl border border-white/10 bg-slate-950/60 p-5 shadow-2xl backdrop-blur lg:col-span-3 lg:sticky lg:top-6 lg:h-[calc(100vh-8rem)]">
                <h3 class="text-xs uppercase tracking-[0.2em] text-slate-400">Main Menu</h3>
                <nav class="mt-5 space-y-2">
                    <a href="#" class="flex items-center justify-between rounded-xl border border-indigo-400/30 bg-indigo-500/20 px-3 py-2 text-sm text-indigo-100">
                        <span>Dashboard</span>
                        <span class="rounded bg-indigo-400/30 px-2 py-0.5 text-xs">Live</span>
                    </a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Sports</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Venues</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Teams</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Players</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Schedules</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Results</a>
                    <a href="#" class="block rounded-xl px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white">Standings</a>
                </nav>

                <div class="mt-8 rounded-2xl border border-cyan-400/25 bg-cyan-500/10 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-cyan-300/80">Current Plan</p>
                    <p class="mt-2 text-lg font-semibold text-cyan-100">Pro School</p>
                    <p class="mt-1 text-sm text-cyan-100/70">Analytics, brackets, and exports are unlocked.</p>
                </div>
            </aside>

            <section class="space-y-5 lg:col-span-9">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border border-white/10 bg-slate-900/80 p-4 shadow-lg shadow-slate-950/60">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total Teams</p>
                        <p class="mt-2 text-3xl font-semibold text-white">128</p>
                        <p class="mt-1 text-xs text-emerald-300">+9.8% this semester</p>
                    </article>
                    <article class="rounded-2xl border border-white/10 bg-slate-900/80 p-4 shadow-lg shadow-slate-950/60">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Active Players</p>
                        <p class="mt-2 text-3xl font-semibold text-white">5,640</p>
                        <p class="mt-1 text-xs text-emerald-300">+12.4% this week</p>
                    </article>
                    <article class="rounded-2xl border border-white/10 bg-slate-900/80 p-4 shadow-lg shadow-slate-950/60">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Matches Today</p>
                        <p class="mt-2 text-3xl font-semibold text-white">34</p>
                        <p class="mt-1 text-xs text-amber-300">5 pending venue confirmation</p>
                    </article>
                    <article class="rounded-2xl border border-white/10 bg-slate-900/80 p-4 shadow-lg shadow-slate-950/60">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Completion Rate</p>
                        <p class="mt-2 text-3xl font-semibold text-white">94.2%</p>
                        <p class="mt-1 text-xs text-emerald-300">No critical blockers</p>
                    </article>
                </div>

                <div class="grid gap-5 xl:grid-cols-3">
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 xl:col-span-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-slate-100">Most Visited Sport Facilities</h3>
                            <span class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">Apr 25 - Apr 29</span>
                        </div>
                        <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/50 p-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-sm text-slate-300">School Main Court</p>
                                    <p class="mt-1 text-2xl font-semibold text-cyan-200">31,250 visits</p>
                                </div>
                                <div class="space-y-2 text-sm text-slate-300">
                                    <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2"><span>North Gym</span><span>18,420</span></div>
                                    <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2"><span>Riverside Field</span><span>13,690</span></div>
                                    <div class="flex items-center justify-between rounded-lg bg-white/5 px-3 py-2"><span>Aquatic Center</span><span>9,510</span></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                        <h3 class="text-base font-semibold text-slate-100">Feature Usage</h3>
                        <div class="mt-4 space-y-3">
                            <div>
                                <div class="mb-1 flex justify-between text-xs text-slate-300"><span>Bracket Generator</span><span>72%</span></div>
                                <div class="h-2 rounded-full bg-slate-700"><div class="h-2 w-[72%] rounded-full bg-cyan-400"></div></div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between text-xs text-slate-300"><span>Standings Module</span><span>89%</span></div>
                                <div class="h-2 rounded-full bg-slate-700"><div class="h-2 w-[89%] rounded-full bg-indigo-400"></div></div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between text-xs text-slate-300"><span>Exports</span><span>47%</span></div>
                                <div class="h-2 rounded-full bg-slate-700"><div class="h-2 w-[47%] rounded-full bg-amber-400"></div></div>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="grid gap-5 xl:grid-cols-3">
                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5 xl:col-span-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-slate-100">Task Schedule</h3>
                            <span class="text-xs text-slate-400">Apr 25, 2026</span>
                        </div>
                        <div class="mt-5 space-y-3">
                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-white">Volleyball Semi Finals - Court Assignment</p>
                                    <p class="text-xs text-slate-400">08:00 - 10:00</p>
                                </div>
                                <span class="text-xs text-cyan-300">Facilitator Team A</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-white">Basketball Division B - Referee Confirmation</p>
                                    <p class="text-xs text-slate-400">11:00 - 12:30</p>
                                </div>
                                <span class="text-xs text-indigo-300">Operations Desk</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-white">Football Group Stage - Result Verification</p>
                                    <p class="text-xs text-slate-400">14:00 - 15:30</p>
                                </div>
                                <span class="text-xs text-amber-300">Data Review</span>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-2xl border border-white/10 bg-slate-900/85 p-5">
                        <h3 class="text-base font-semibold text-slate-100">Latest Activity</h3>
                        <ul class="mt-4 space-y-3">
                            <li class="rounded-xl bg-white/5 px-3 py-2 text-sm text-slate-200">Coach Miguel updated Team Falcons roster.</li>
                            <li class="rounded-xl bg-white/5 px-3 py-2 text-sm text-slate-200">Main Court marked unavailable for 7:00 PM slot.</li>
                            <li class="rounded-xl bg-white/5 px-3 py-2 text-sm text-slate-200">Standings recalculated for Basketball Division A.</li>
                            <li class="rounded-xl bg-white/5 px-3 py-2 text-sm text-slate-200">Pro export generated for weekly sports report.</li>
                        </ul>
                    </article>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
