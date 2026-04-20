<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-cyan-700/80 dark:text-cyan-300/80">Central App</p>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">School Management</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('central.universities.create') }}" class="rounded-xl border border-cyan-300/40 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-900 hover:bg-cyan-500/15 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">
                    Add School
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/80">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">School Name</th>
                        <th class="px-4 py-3 text-left font-medium">School Address</th>
                        <th class="px-4 py-3 text-left font-medium">Domain</th>
                        <th class="px-4 py-3 text-left font-medium">Admin Name</th>
                        <th class="px-4 py-3 text-left font-medium">Admin Email</th>
                        <th class="px-4 py-3 text-left font-medium">Subscription</th>
                        <th class="px-4 py-3 text-left font-medium">Starts</th>
                        <th class="px-4 py-3 text-left font-medium">Expires</th>
                        <th class="px-4 py-3 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($universities as $university)
                        @php
                            $subscription = $university->subscription;
                            $plan = $subscription?->plan ?? $university->plan;
                            $status = $subscription?->status ?? $university->status;
                            $startsAt = $subscription?->start_date ?? $university->subscription_starts_at;
                            $dueDate = $subscription?->due_date ?? $university->expires_at;
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $university->name }}</p>
                                <p class="text-xs text-slate-400">ID: {{ $university->id }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $university->school_address ?? 'Not provided' }}</td>
                            <td class="px-4 py-3">{{ optional($university->domains->first())->domain ?? 'No domain' }}</td>
                            <td class="px-4 py-3">{{ $university->tenant_admin_name ?? 'Not provided' }}</td>
                            <td class="px-4 py-3">{{ $university->tenant_admin_email ?? 'Not provided' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-cyan-500/20 px-2 py-1 text-xs uppercase text-cyan-100">{{ $plan }}</span>
                                    <span class="rounded-full px-2 py-1 text-xs {{ $status === 'active' ? 'bg-emerald-500/20 text-emerald-200' : 'bg-amber-500/20 text-amber-200' }}">
                                        {{ $status }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ strtoupper((string) ($subscription?->billing_cycle ?? 'monthly')) }}
                                    · Final: ${{ number_format((float) ($subscription?->final_price ?? 0), 2) }}
                                </p>
                            </td>
                            <td class="px-4 py-3">{{ $startsAt?->format('M d, Y') ?? 'Not set' }}</td>
                            <td class="px-4 py-3">{{ $dueDate?->format('M d, Y') ?? 'No expiry' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($status === 'pending')
                                        <form method="POST" action="{{ route('central.universities.approve', $university) }}" onsubmit="return confirm('Approve this school and send tenant admin invite?');" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" step="0.01" min="0" name="manual_price_override" placeholder="Manual final price" class="w-36 rounded border border-white/10 bg-slate-950/70 px-2 py-1 text-xs text-slate-100">
                                            <button type="submit" class="rounded-md border border-emerald-300/30 bg-emerald-500/20 px-3 py-1 text-xs text-emerald-100 hover:bg-emerald-500/30">Approve</button>
                                        </form>
                                    @endif

                                    <a href="{{ route('central.universities.edit', $university) }}" class="rounded-md border border-white/10 bg-white/5 px-3 py-1 text-xs hover:bg-white/10">Edit</a>

                                    <form method="POST" action="{{ route('central.universities.destroy', $university) }}" onsubmit="return confirm('Delete this tenant and its database? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-300/30 bg-rose-500/20 px-3 py-1 text-xs text-rose-100 hover:bg-rose-500/30">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-slate-400">No schools found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $universities->links() }}
        </div>
    </div>
</x-app-layout>
