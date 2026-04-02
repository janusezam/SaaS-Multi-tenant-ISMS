<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Plan Management</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Plan</h3>
            <form method="POST" action="{{ route('central.business-control.plans.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf
                <input type="text" name="code" placeholder="code" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <input type="text" name="name" placeholder="name" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <input type="number" step="0.01" min="0" name="sort_order" placeholder="sort order" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                <input type="number" step="0.01" min="0" name="monthly_price" placeholder="monthly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <input type="number" step="0.01" min="0" name="yearly_price" placeholder="yearly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 md:col-span-3">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                    Active
                </label>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Feature Flags</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="feature_flags[analytics]" value="1" class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                            Analytics Dashboard
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="feature_flags[bracket]" value="1" class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                            Bracket Generator
                        </label>
                    </div>
                </div>
                <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Create Plan</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-950/60 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Monthly</th>
                        <th class="px-4 py-3 text-left">Yearly</th>
                        <th class="px-4 py-3 text-left">Yearly Save %</th>
                        <th class="px-4 py-3 text-left">Feature Flags</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800 dark:divide-white/10 dark:text-slate-200">
                    @foreach ($plans as $plan)
                        <tr>
                            <td class="px-4 py-3">{{ $plan->code }}</td>
                            <td class="px-4 py-3">{{ $plan->name }}</td>
                            <td class="px-4 py-3">${{ number_format((float) $plan->monthly_price, 2) }}</td>
                            <td class="px-4 py-3">${{ number_format((float) $plan->yearly_price, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format((float) $plan->yearly_discount_percent, 2) }}%</td>
                            <td class="px-4 py-3 text-xs">
                                <div class="flex flex-wrap gap-1">
                                    @php
                                        $flags = is_array($plan->feature_flags) ? $plan->feature_flags : [];
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2 py-0.5 {{ ($flags['analytics'] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100' : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300' }}">analytics: {{ ($flags['analytics'] ?? false) ? 'on' : 'off' }}</span>
                                    <span class="inline-flex rounded-full border px-2 py-0.5 {{ ($flags['bracket'] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100' : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300' }}">bracket: {{ ($flags['bracket'] ?? false) ? 'on' : 'off' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/20 dark:text-emerald-100' => $plan->is_active,
                                    'border-slate-300 bg-slate-100 text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300' => ! $plan->is_active,
                                ])>
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('central.business-control.plans.update', $plan) }}" class="grid gap-2 lg:grid-cols-4">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="code" value="{{ $plan->code }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="text" name="name" value="{{ $plan->name }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="number" step="0.01" min="0" name="monthly_price" value="{{ $plan->monthly_price }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="number" step="0.01" min="0" name="yearly_price" value="{{ $plan->yearly_price }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <input type="text" value="{{ number_format((float) $plan->yearly_discount_percent, 2) }}% (auto)" class="rounded border border-slate-300 bg-slate-100 text-slate-600 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-300" readonly>
                                    <input type="number" min="1" max="9999" name="sort_order" value="{{ $plan->sort_order }}" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                    <select name="is_active" class="rounded border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                                        <option value="1" @selected($plan->is_active)>Active</option>
                                        <option value="0" @selected(! $plan->is_active)>Inactive</option>
                                    </select>
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="feature_flags[analytics]" value="0">
                                        <input type="checkbox" name="feature_flags[analytics]" value="1" @checked(($flags['analytics'] ?? false)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                        Analytics Dashboard
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="feature_flags[bracket]" value="0">
                                        <input type="checkbox" name="feature_flags[bracket]" value="1" @checked(($flags['bracket'] ?? false)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                        Bracket Generator
                                    </label>
                                    <button type="submit" class="rounded border border-cyan-300 bg-cyan-100 px-3 py-1 text-cyan-800 dark:border-cyan-300/30 dark:bg-cyan-500/20 dark:text-cyan-100">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($plans->isEmpty())
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No plans found yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>
    </div>
</x-app-layout>
