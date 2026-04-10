<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Pending Registration</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6 text-slate-200">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Name</dt>
                    <dd class="mt-1 text-sm">{{ $registration->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Email</dt>
                    <dd class="mt-1 text-sm">{{ $registration->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Phone</dt>
                    <dd class="mt-1 text-sm">{{ $registration->phone }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Requested Role</dt>
                    <dd class="mt-1 text-sm">{{ str_replace('_', ' ', ucfirst($registration->role)) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs uppercase tracking-[0.16em] text-slate-400">Requested At</dt>
                    <dd class="mt-1 text-sm">{{ $registration->created_at?->toDayDateTimeString() }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-2">
                <form method="POST" action="{{ route('tenant.users.pending.approve', $registration) }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-emerald-300/30 bg-emerald-500/20 px-4 py-2 text-sm text-emerald-100 hover:bg-emerald-500/30">Approve and Send Invite</button>
                </form>

                <form method="POST" action="{{ route('tenant.users.pending.destroy', $registration) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-4 py-2 text-sm text-rose-100 hover:bg-rose-500/30">Delete Request</button>
                </form>

                <a href="{{ route('tenant.users.index') }}" class="rounded-md border border-white/15 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back to Users</a>
            </div>
        </div>
    </div>
</x-app-layout>
