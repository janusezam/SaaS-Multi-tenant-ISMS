<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-700/80 dark:text-cyan-300/80">Central App</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Support &amp; Updates</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Review tenant issue reports and publish product updates.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-300/30 dark:bg-emerald-500/15 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-300/30 dark:bg-rose-500/10 dark:text-rose-100">
                <p class="font-semibold">Please fix the errors below:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="isms-surface rounded-2xl p-6 xl:col-span-2">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Open Tenant Reports</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Issues submitted from tenant settings modules.</p>

                <div class="mt-5 space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse ($openTickets as $ticket)
                        <article class="rounded-xl border border-slate-200 bg-white/60 p-4 dark:border-white/10 dark:bg-slate-950/50">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $ticket->subject }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ strtoupper($ticket->category) }} · {{ $ticket->tenant_name }} ({{ $ticket->tenant_id }}) · {{ $ticket->reported_by_name }}
                                    </p>
                                </div>
                                <span class="rounded-full border border-slate-300 px-2 py-0.5 text-[11px] uppercase tracking-[0.15em] text-slate-600 dark:border-white/20 dark:text-slate-300">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </div>

                            <p class="mt-3 text-sm text-slate-700 dark:text-slate-300">{{ $ticket->message }}</p>

                            <form method="POST" action="{{ route('central.business-control.support-updates.tickets.update', $ticket) }}" class="mt-4 grid gap-3 md:grid-cols-[200px_1fr_auto]">
                                @csrf
                                @method('PATCH')

                                <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                                    <option value="open" @selected($ticket->status === 'open')>Open</option>
                                    <option value="in_progress" @selected($ticket->status === 'in_progress')>In Progress</option>
                                    <option value="resolved" @selected($ticket->status === 'resolved')>Resolved</option>
                                </select>

                                <input type="text" name="central_note" value="{{ $ticket->central_note }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder:text-slate-500" placeholder="Internal note to tenant thread">

                                <button type="submit" class="rounded-lg border border-cyan-300/40 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-900 hover:bg-cyan-500/15 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">Update</button>
                            </form>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">No open support reports right now.</p>
                    @endforelse
                </div>
            </section>

            <section class="space-y-6">
                <div class="isms-surface rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Publish Update</h3>
                    <form method="POST" action="{{ route('central.business-control.support-updates.updates.store') }}" class="mt-4 space-y-3">
                        @csrf

                        <div>
                            <label for="title" class="text-xs uppercase tracking-wide text-slate-600 dark:text-slate-300">Title</label>
                            <input id="title" name="title" type="text" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder:text-slate-500" value="{{ old('title') }}" required>
                        </div>

                        <div>
                            <label for="version" class="text-xs uppercase tracking-wide text-slate-600 dark:text-slate-300">Version</label>
                            <input id="version" name="version" type="text" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder:text-slate-500" value="{{ old('version', $suggestedUpdateVersion ?? '') }}" placeholder="{{ $suggestedUpdateVersion ?? 'v1.0.0' }}">
                        </div>

                        <input type="hidden" name="source" value="github">

                        <div>
                            <label for="summary" class="text-xs uppercase tracking-wide text-slate-600 dark:text-slate-300">Summary</label>
                            <textarea id="summary" name="summary" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-white/5 dark:text-slate-100 dark:placeholder:text-slate-500">{{ old('summary') }}</textarea>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="checkbox" name="is_published" value="1" checked class="rounded border-slate-300 bg-white text-cyan-600 focus:ring-cyan-300/40 dark:border-white/20 dark:bg-white/5 dark:text-cyan-400">
                                Publish now
                            </label>
                            <button type="submit" class="rounded-lg border border-cyan-300/40 bg-cyan-500/10 px-4 py-2 text-sm font-semibold text-cyan-900 hover:bg-cyan-500/15 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">Post Update</button>
                        </div>
                    </form>
                </div>

                <div class="isms-surface rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Published Updates</h3>
                    <div class="mt-4 space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse ($updates as $update)
                            <div class="rounded-xl border border-slate-200 bg-white/60 p-4 dark:border-white/10 dark:bg-slate-950/40">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $update->title }}</p>
                                <p class="mt-1 text-xs text-cyan-200">{{ $update->version ?? 'N/A' }}</p>
                                @if (!empty($update->summary))
                                    <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $update->summary }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No updates posted yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <section class="isms-surface rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-slate-100">Recently Resolved Reports</h3>
            <div class="mt-4 space-y-3">
                @forelse ($resolvedTickets as $ticket)
                    <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-100">{{ $ticket->subject }}</p>
                            <p class="text-xs text-slate-400">Resolved {{ $ticket->resolved_at?->diffForHumans() ?? 'N/A' }}</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ $ticket->tenant_name }} · {{ $ticket->reported_by_email }}</p>
                        @if (!empty($ticket->central_note))
                            <p class="mt-2 text-sm text-emerald-200">{{ $ticket->central_note }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No resolved reports yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
