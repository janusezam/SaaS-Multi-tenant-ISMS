<x-app-layout>
    @php
        $oldForm = (string) old('_form', '');
        $openCreate = $errors->any() && $oldForm === 'create';
        $openEditId = null;

        if ($errors->any() && str_starts_with($oldForm, 'edit-')) {
            $openEditId = (int) str_replace('edit-', '', $oldForm);
        }
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Campaign Management</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ createOpen: @js($openCreate), editOpen: @js($openEditId) }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('central.business-control.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-100 dark:hover:bg-slate-800/80">Back to Business Control</a>
            <button type="button" @click="createOpen = true" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">
                + Create Campaign
            </button>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-300/30 dark:bg-emerald-500/10 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-300/30 dark:bg-rose-500/10 dark:text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($campaigns as $campaign)
                @php
                    $targetPlans = is_array($campaign->target_plan_codes) ? $campaign->target_plan_codes : [];
                    $statusClasses = match ($campaign->status) {
                        'active' => 'border-emerald-300/40 bg-emerald-500/15 text-emerald-100',
                        'draft' => 'border-amber-300/40 bg-amber-500/15 text-amber-100',
                        default => 'border-slate-300/30 bg-slate-500/20 text-slate-200',
                    };
                @endphp

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-slate-900/85">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $campaign->name }}</h3>
                            <p class="mt-1 text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Policy: {{ str_replace('_', ' ', $campaign->lifecycle_policy) }}</p>
                        </div>
                        <button type="button" @click="editOpen = {{ $campaign->id }}" class="inline-flex items-center justify-center rounded-full border border-cyan-300/40 bg-cyan-500/15 px-3 py-1 text-xs font-semibold text-cyan-100 hover:bg-cyan-500/25">
                            Edit
                        </button>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full border px-2.5 py-1 {{ $statusClasses }}">{{ strtoupper($campaign->status) }}</span>
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                        <p><span class="font-semibold">Rule:</span> {{ strtoupper($campaign->discount_type) }} {{ number_format((float) $campaign->discount_value, 2) }}</p>
                        <p><span class="font-semibold">Target:</span> {{ $targetPlans === [] ? 'ALL PLANS' : strtoupper(implode(', ', $targetPlans)) }}</p>
                        <p><span class="font-semibold">Starts:</span> {{ $campaign->starts_at?->format('M d, Y h:i A') ?: 'No start' }}</p>
                        <p><span class="font-semibold">Ends:</span> {{ $campaign->ends_at?->format('M d, Y h:i A') ?: 'No end' }}</p>
                    </div>

                    @if ($campaign->description)
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:border-white/10 dark:bg-slate-950/40 dark:text-slate-300">
                            {{ $campaign->description }}
                        </div>
                    @endif

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-white/10 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Version History</p>

                        @if ($campaign->versions->isEmpty())
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">No versions yet.</p>
                        @else
                            <ul class="mt-2 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
                                @foreach ($campaign->versions as $version)
                                    <li class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 dark:border-white/10 dark:bg-slate-900/70">
                                        <span class="font-semibold">v{{ $version->version_number }}</span>
                                        <span class="truncate">{{ $version->name }}</span>
                                        <span class="shrink-0 text-slate-500 dark:text-slate-400">{{ $version->created_at?->format('M d, Y h:i A') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500 shadow-sm dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-400">
                    No campaigns found yet.
                </div>
            @endforelse
        </div>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-40 overflow-y-auto p-4 sm:p-6" aria-modal="true" role="dialog">
            <div class="fixed inset-0 bg-slate-950/70" @click="createOpen = false"></div>
            <div class="relative mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-slate-900/95">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Campaign</h3>
                    <button type="button" @click="createOpen = false" class="rounded-lg border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-100 dark:border-white/15 dark:text-slate-300 dark:hover:bg-slate-800">Close</button>
                </div>

                <form method="POST" action="{{ route('central.business-control.campaigns.store') }}" class="grid gap-3 md:grid-cols-3">
                    @csrf
                    <input type="hidden" name="_form" value="create">

                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Campaign name (e.g. Black Friday 2026)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <select name="status" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <option value="draft" @selected((string) old('status', 'draft') === 'draft')>Draft</option>
                        <option value="active" @selected((string) old('status') === 'active')>Active</option>
                        <option value="inactive" @selected((string) old('status') === 'inactive')>Inactive</option>
                    </select>
                    <div></div>

                    <select name="discount_type" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <option value="percent" @selected((string) old('discount_type', 'percent') === 'percent')>Percent</option>
                        <option value="fixed" @selected((string) old('discount_type') === 'fixed')>Fixed</option>
                    </select>
                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value') }}" placeholder="Discount value" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <select name="lifecycle_policy" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <option value="next_renewal" @selected((string) old('lifecycle_policy', 'next_renewal') === 'next_renewal')>Apply on next renewal</option>
                    </select>

                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Starts At</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Ends At</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    </div>
                    <div></div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Target Plans (empty = all plans)</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-3">
                            @foreach ($plans as $plan)
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <input type="checkbox" name="target_plan_codes[]" value="{{ $plan->code }}" @checked(in_array($plan->code, old('target_plan_codes', []), true)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                    {{ strtoupper($plan->code) }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <textarea name="description" rows="2" placeholder="Campaign notes" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">{{ old('description') }}</textarea>

                    <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Create Campaign</button>
                </form>
            </div>
        </div>

        @foreach ($campaigns as $campaign)
            @php
                $campaignTargetPlans = is_array($campaign->target_plan_codes) ? $campaign->target_plan_codes : [];
                $isEditingThisCampaign = $oldForm === 'edit-'.$campaign->id;
            @endphp

            <div x-show="editOpen === {{ $campaign->id }}" x-cloak class="fixed inset-0 z-40 overflow-y-auto p-4 sm:p-6" aria-modal="true" role="dialog">
                <div class="fixed inset-0 bg-slate-950/70" @click="editOpen = null"></div>
                <div class="relative mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-slate-900/95">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Edit {{ $campaign->name }}</h3>
                        <button type="button" @click="editOpen = null" class="rounded-lg border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-100 dark:border-white/15 dark:text-slate-300 dark:hover:bg-slate-800">Close</button>
                    </div>

                    <form method="POST" action="{{ route('central.business-control.campaigns.update', $campaign) }}" class="grid gap-3 md:grid-cols-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit-{{ $campaign->id }}">

                        <input type="text" name="name" value="{{ $isEditingThisCampaign ? old('name', $campaign->name) : $campaign->name }}" placeholder="Campaign name" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <select name="status" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                            <option value="draft" @selected((string) ($isEditingThisCampaign ? old('status', $campaign->status) : $campaign->status) === 'draft')>Draft</option>
                            <option value="active" @selected((string) ($isEditingThisCampaign ? old('status', $campaign->status) : $campaign->status) === 'active')>Active</option>
                            <option value="inactive" @selected((string) ($isEditingThisCampaign ? old('status', $campaign->status) : $campaign->status) === 'inactive')>Inactive</option>
                        </select>
                        <div></div>

                        <select name="discount_type" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                            <option value="percent" @selected((string) ($isEditingThisCampaign ? old('discount_type', $campaign->discount_type) : $campaign->discount_type) === 'percent')>Percent</option>
                            <option value="fixed" @selected((string) ($isEditingThisCampaign ? old('discount_type', $campaign->discount_type) : $campaign->discount_type) === 'fixed')>Fixed</option>
                        </select>
                        <input type="number" step="0.01" min="0" name="discount_value" value="{{ $isEditingThisCampaign ? old('discount_value', (string) $campaign->discount_value) : $campaign->discount_value }}" placeholder="Discount value" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <select name="lifecycle_policy" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                            <option value="next_renewal" @selected((string) ($isEditingThisCampaign ? old('lifecycle_policy', $campaign->lifecycle_policy) : $campaign->lifecycle_policy) === 'next_renewal')>Apply on next renewal</option>
                        </select>

                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Starts At</label>
                            <input type="datetime-local" name="starts_at" value="{{ $isEditingThisCampaign ? old('starts_at', $campaign->starts_at?->format('Y-m-d\\TH:i')) : $campaign->starts_at?->format('Y-m-d\\TH:i') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Ends At</label>
                            <input type="datetime-local" name="ends_at" value="{{ $isEditingThisCampaign ? old('ends_at', $campaign->ends_at?->format('Y-m-d\\TH:i')) : $campaign->ends_at?->format('Y-m-d\\TH:i') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        </div>
                        <div></div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Target Plans (empty = all plans)</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                @foreach ($plans as $plan)
                                    @php
                                        $selectedTargetPlans = $isEditingThisCampaign ? old('target_plan_codes', $campaignTargetPlans) : $campaignTargetPlans;
                                    @endphp
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                        <input type="checkbox" name="target_plan_codes[]" value="{{ $plan->code }}" @checked(in_array($plan->code, $selectedTargetPlans, true)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                        {{ strtoupper($plan->code) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <textarea name="description" rows="2" placeholder="Campaign notes" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">{{ $isEditingThisCampaign ? old('description', $campaign->description) : $campaign->description }}</textarea>

                        <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Save Changes</button>
                    </form>

                    <form method="POST" action="{{ route('central.business-control.campaigns.apply-renewals', $campaign) }}" class="mt-4 grid gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 sm:grid-cols-4 dark:border-amber-300/20 dark:bg-amber-500/10">
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
                        <button type="submit" class="rounded border border-amber-300 bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-200 dark:border-amber-300/30 dark:bg-amber-500/20 dark:text-amber-100">Apply on next renewal</button>
                    </form>
                </div>
            </div>
        @endforeach

        <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900/80">
            <p class="mb-2 text-xs text-slate-600 dark:text-slate-300">Lifecycle policy is currently set to: apply campaign on next renewal (no mid-cycle repricing/proration).</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Active subscriptions tracked: {{ number_format((int) $activeSubscriptionCount) }}</p>
            <div class="mt-3">{{ $campaigns->links() }}</div>
        </div>
    </div>
</x-app-layout>
