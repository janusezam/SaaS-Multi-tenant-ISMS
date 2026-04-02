<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Coupon Management</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Coupon</h3>
            <form method="POST" action="{{ route('central.business-control.coupons.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf
                <input type="text" name="code" placeholder="code" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <input type="text" name="internal_note" placeholder="internal note (optional)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                <select name="discount_type" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <option value="percent">Percent</option>
                    <option value="fixed">Fixed</option>
                </select>
                <input type="number" step="0.01" min="0" name="discount_value" placeholder="discount value" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <select name="applies_to_plan" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <option value="">All plans</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->code }}">{{ strtoupper($plan->code) }}</option>
                    @endforeach
                </select>
                <input type="number" min="1" name="usage_limit" placeholder="usage limit" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400" for="coupon-starts-at">Valid from</label>
                    <input id="coupon-starts-at" type="datetime-local" name="starts_at" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400" for="coupon-expires-at">Expires at</label>
                    <input id="coupon-expires-at" type="datetime-local" name="expires_at" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                    Active
                </label>
                <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Create Coupon</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Value</th>
                        <th class="px-4 py-3 text-left">Plan</th>
                        <th class="px-4 py-3 text-left">Usage</th>
                        <th class="px-4 py-3 text-left">Expiry</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800 dark:divide-white/10 dark:text-slate-200">
                    @foreach ($coupons as $coupon)
                        <tr>
                            <td class="px-4 py-3">{{ $coupon->code }}</td>
                            <td class="px-4 py-3">{{ strtoupper($coupon->discount_type) }}</td>
                            <td class="px-4 py-3">{{ $coupon->discount_value }}</td>
                            <td class="px-4 py-3">{{ $coupon->applies_to_plan ? strtoupper($coupon->applies_to_plan) : 'ALL' }}</td>
                            <td class="px-4 py-3">{{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $coupon->expires_at?->format('M d, Y h:i A') ?? 'No expiry' }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100' => $coupon->is_active,
                                    'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300' => ! $coupon->is_active,
                                ])>
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('central.business-control.coupons.update', $coupon) }}" class="grid gap-2 lg:grid-cols-4">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="code" value="{{ $coupon->code }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="text" name="internal_note" value="{{ $coupon->internal_note }}" placeholder="internal note (optional)" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <select name="discount_type" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <option value="percent" @selected($coupon->discount_type === 'percent')>Percent</option>
                                        <option value="fixed" @selected($coupon->discount_type === 'fixed')>Fixed</option>
                                    </select>
                                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ $coupon->discount_value }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <select name="applies_to_plan" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <option value="">All plans</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->code }}" @selected($coupon->applies_to_plan === $plan->code)>{{ strtoupper($plan->code) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" min="1" name="usage_limit" value="{{ $coupon->usage_limit }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="datetime-local" name="starts_at" value="{{ $coupon->starts_at?->format('Y-m-d\TH:i') }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="datetime-local" name="expires_at" value="{{ $coupon->expires_at?->format('Y-m-d\TH:i') }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <select name="is_active" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <option value="1" @selected($coupon->is_active)>Active</option>
                                        <option value="0" @selected(! $coupon->is_active)>Inactive</option>
                                    </select>
                                    <div class="flex gap-2">
                                        <button type="submit" class="rounded border border-cyan-300 bg-cyan-100 px-3 py-1 text-cyan-800 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100">Update</button>
                                        <a href="{{ route('central.business-control.coupons.redemptions', $coupon) }}" class="rounded border border-amber-300 bg-amber-100 px-3 py-1 text-xs text-amber-800 dark:border-amber-300/30 dark:bg-amber-500/20 dark:text-amber-100">History</a>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($coupons->isEmpty())
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No coupons found yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            {{ $coupons->links() }}
        </div>
    </div>
</x-app-layout>
