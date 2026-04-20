<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-700/80 dark:text-cyan-300/80">Central App</p>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Subscription Notification Logs</h2>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('central.subscription-notification-logs.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-white/10 dark:bg-slate-900/80">
            <div>
                <label for="tenant_id" class="mb-1 block text-xs text-slate-600 dark:text-slate-300">Tenant</label>
                <select id="tenant_id" name="tenant_id" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <option value="">All</option>
                    @foreach ($tenantOptions as $tenantOption)
                        <option value="{{ $tenantOption->id }}" @selected(request('tenant_id') === $tenantOption->id)>
                            {{ $tenantOption->name }} ({{ $tenantOption->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="notification_type" class="mb-1 block text-xs text-slate-600 dark:text-slate-300">Type</label>
                <select id="notification_type" name="notification_type" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <option value="">All</option>
                    @foreach ($notificationTypeOptions as $type)
                        <option value="{{ $type }}" @selected(request('notification_type') === $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="recipient_email" class="mb-1 block text-xs text-slate-600 dark:text-slate-300">Recipient</label>
                <input id="recipient_email" name="recipient_email" value="{{ request('recipient_email') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 dark:placeholder:text-slate-500" />
            </div>

            <div>
                <label for="from_date" class="mb-1 block text-xs text-slate-600 dark:text-slate-300">From Date</label>
                <input id="from_date" type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" />
            </div>

            <div>
                <label for="to_date" class="mb-1 block text-xs text-slate-600 dark:text-slate-300">To Date</label>
                <input id="to_date" type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" />
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-900 hover:bg-cyan-500/15 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">Filter</button>
                <a href="{{ route('central.subscription-notification-logs.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/80">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-950/60 text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Queued At</th>
                        <th class="px-4 py-3 text-left font-medium">Tenant</th>
                        <th class="px-4 py-3 text-left font-medium">Recipient</th>
                        <th class="px-4 py-3 text-left font-medium">Type</th>
                        <th class="px-4 py-3 text-left font-medium">Subject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 text-slate-200">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-3">{{ $log->queued_at?->format('M d, Y H:i') ?? $log->created_at?->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $log->university?->name ?? $log->university_id }}</td>
                            <td class="px-4 py-3">{{ $log->recipient_email }}</td>
                            <td class="px-4 py-3">{{ $log->notification_type }}</td>
                            <td class="px-4 py-3">{{ $log->subject }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No notification logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
