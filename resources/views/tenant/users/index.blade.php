<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Tenant Users</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">{{ session('status') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('tenant.users.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Add User</a>
        </div>

        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-amber-300/20 dark:bg-amber-500/10">
            <div class="border-b border-slate-200 px-4 py-3 dark:border-amber-300/20">
                <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-800 dark:text-amber-100">Pending Account Requests</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-amber-300/20">
                    <thead class="bg-slate-50 text-slate-700 dark:bg-slate-950/35 dark:text-amber-100/90">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">Phone</th>
                            <th class="px-4 py-3 text-left font-medium">Role</th>
                            <th class="px-4 py-3 text-left font-medium">Requested</th>
                            <th class="px-4 py-3 text-left font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 dark:divide-amber-300/20 dark:text-amber-50">
                        @forelse ($pendingRegistrations as $registration)
                            <tr>
                                <td class="px-4 py-3">{{ $registration->name }}</td>
                                <td class="px-4 py-3">{{ $registration->email }}</td>
                                <td class="px-4 py-3">{{ $registration->phone }}</td>
                                <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($registration->role)) }}</td>
                                <td class="px-4 py-3">{{ $registration->created_at?->diffForHumans() }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('tenant.users.pending.show', $registration) }}" class="rounded-md border border-slate-200 bg-slate-50 px-3 py-1 text-xs hover:bg-slate-100 dark:border-white/15 dark:bg-white/10 dark:hover:bg-white/15">View</a>

                                        <form method="POST" action="{{ route('tenant.users.pending.approve', $registration) }}">
                                            @csrf
                                            <button type="submit" class="rounded-md border border-emerald-300/30 bg-emerald-500/20 px-3 py-1 text-xs text-white hover:bg-emerald-500/30 dark:text-emerald-100">Accept</button>
                                        </form>

                                        <form method="POST" action="{{ route('tenant.users.pending.destroy', $registration) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-slate-500 dark:text-amber-100/70">No pending account requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/85">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-left font-medium">Role</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($users as $tenantUser)
                        <tr>
                            <td class="px-4 py-3">{{ $tenantUser->name }}</td>
                            <td class="px-4 py-3">{{ $tenantUser->email }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($tenantUser->role)) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    @if ($tenantUser->role !== 'university_admin')
                                        <a href="{{ route('tenant.users.edit', $tenantUser) }}" class="rounded-md border border-white/10 bg-white/5 px-3 py-1 text-xs hover:bg-white/10">Edit</a>
                                        <form method="POST" action="{{ route('tenant.users.destroy', $tenantUser) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                        </form>
                                    @else
                                        <span class="rounded-md border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">Protected</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-400">No tenant users available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
