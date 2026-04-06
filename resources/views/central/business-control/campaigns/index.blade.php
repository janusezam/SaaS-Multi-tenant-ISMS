<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Campaign Management</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <a href="{{ route('central.business-control.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-100 dark:hover:bg-slate-800/80">Back to Business Control</a>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Campaign</h3>
            <form method="POST" action="{{ route('central.business-control.campaigns.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf
                <input type="text" name="name" placeholder="Campaign name (e.g. Black Friday 2026)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <select name="status" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <input type="number" name="priority" min="1" max="9999" value="100" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>

                <select name="discount_type" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
                <input type="number" step="0.01" min="0" name="discount_value" placeholder="Discount value" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <select name="lifecycle_policy" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <option value="next_renewal">Apply on next renewal</option>
                </select>

                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Starts At</label>
                    <input type="datetime-local" name="starts_at" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Ends At</label>
                    <input type="datetime-local" name="ends_at" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_stackable_with_coupon" value="1" checked class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                    Stackable with coupons
                </label>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Target Plans (empty = all plans)</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-3">
                        @foreach ($plans as $plan)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="checkbox" name="target_plan_codes[]" value="{{ $plan->code }}" class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                {{ strtoupper($plan->code) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <textarea name="description" rows="2" placeholder="Campaign notes" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3"></textarea>

                <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Create Campaign</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-100 text-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-left">Campaign</th>
                            <th class="px-4 py-3 text-left">Rule</th>
                            <th class="px-4 py-3 text-left">Target</th>
                            <th class="px-4 py-3 text-left">Window</th>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800 dark:divide-white/10 dark:text-slate-200">
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium">{{ $campaign->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ strtoupper($campaign->status) }} · Policy: {{ str_replace('_', ' ', $campaign->lifecycle_policy) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    {{ strtoupper($campaign->discount_type) }} {{ number_format((float) $campaign->discount_value, 2) }}
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $campaign->is_stackable_with_coupon ? 'Stackable with coupons' : 'Coupon override mode' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @php
                                        $targetPlans = is_array($campaign->target_plan_codes) ? $campaign->target_plan_codes : [];
                                    @endphp
                                    {{ $targetPlans === [] ? 'ALL PLANS' : strtoupper(implode(', ', $targetPlans)) }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $campaign->starts_at?->format('M d, Y h:i A') ?: 'No start' }}<br>
                                    {{ $campaign->ends_at?->format('M d, Y h:i A') ?: 'No end' }}
                                </td>
                                <td class="px-4 py-3">{{ $campaign->priority }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('central.business-control.campaigns.update', $campaign) }}" class="grid gap-2 lg:grid-cols-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="name" value="{{ $campaign->name }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <select name="status" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                            <option value="draft" @selected($campaign->status === 'draft')>Draft</option>
                                            <option value="active" @selected($campaign->status === 'active')>Active</option>
                                            <option value="inactive" @selected($campaign->status === 'inactive')>Inactive</option>
                                        </select>
                                        <input type="number" min="1" max="9999" name="priority" value="{{ $campaign->priority }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <select name="discount_type" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                            <option value="percent" @selected($campaign->discount_type === 'percent')>Percent</option>
                                            <option value="fixed" @selected($campaign->discount_type === 'fixed')>Fixed</option>
                                        </select>
                                        <input type="number" step="0.01" min="0" name="discount_value" value="{{ $campaign->discount_value }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <select name="lifecycle_policy" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                            <option value="next_renewal" @selected($campaign->lifecycle_policy === 'next_renewal')>Apply on next renewal</option>
                                        </select>
                                        <input type="datetime-local" name="starts_at" value="{{ $campaign->starts_at?->format('Y-m-d\TH:i') }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <input type="datetime-local" name="ends_at" value="{{ $campaign->ends_at?->format('Y-m-d\TH:i') }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <select name="is_stackable_with_coupon" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                            <option value="1" @selected($campaign->is_stackable_with_coupon)>Stackable</option>
                                            <option value="0" @selected(! $campaign->is_stackable_with_coupon)>Coupon blocked</option>
                                        </select>
                                        <textarea name="description" rows="2" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 lg:col-span-3">{{ $campaign->description }}</textarea>
                                        <div class="lg:col-span-3 grid gap-2 sm:grid-cols-3">
                                            @foreach ($plans as $plan)
                                                <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                                    <input type="checkbox" name="target_plan_codes[]" value="{{ $plan->code }}" @checked(in_array($plan->code, is_array($campaign->target_plan_codes) ? $campaign->target_plan_codes : [], true)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                                    {{ strtoupper($plan->code) }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <button type="submit" class="rounded border border-cyan-300 bg-cyan-100 px-3 py-1 text-cyan-800 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100">Update</button>
                                    </form>

                                    <form method="POST" action="{{ route('central.business-control.campaigns.apply-renewals', $campaign) }}" class="mt-3 grid gap-2 sm:grid-cols-4 rounded-lg border border-amber-200 bg-amber-50 p-2 dark:border-amber-300/20 dark:bg-amber-500/10">
                                        @csrf
                                        <select name="plan_code" class="rounded border border-amber-300 bg-white text-xs text-slate-900 dark:border-amber-300/30 dark:bg-slate-950/70 dark:text-slate-100">
                                            <option value="">All plans</option>
                                            @foreach ($plans as $plan)
                                                <option value="{{ $plan->code }}">{{ strtoupper($plan->code) }}</option>
                                            @endforeach
                                        </select>
                                        <select name="billing_cycle" class="rounded border border-amber-300 bg-white text-xs text-slate-900 dark:border-amber-300/30 dark:bg-slate-950/70 dark:text-slate-100">
                                            <option value="">All cycles</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                        <select name="status" class="rounded border border-amber-300 bg-white text-xs text-slate-900 dark:border-amber-300/30 dark:bg-slate-950/70 dark:text-slate-100">
                                            <option value="active">Active only</option>
                                            <option value="pending">Pending</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                        <button type="submit" class="rounded border border-amber-300 bg-amber-100 px-2 py-1 text-xs text-amber-800 hover:bg-amber-200 dark:border-amber-300/30 dark:bg-amber-500/20 dark:text-amber-100">Apply on next renewal</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if ($campaigns->isEmpty())
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No campaigns found yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <p class="mb-2 text-xs text-slate-600 dark:text-slate-300">Lifecycle policy is currently set to: apply campaign on next renewal (no mid-cycle repricing/proration).</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Active subscriptions tracked: {{ number_format((int) $activeSubscriptionCount) }}</p>
            <div class="mt-3">{{ $campaigns->links() }}</div>
        </div>
    </div>
</x-app-layout>
