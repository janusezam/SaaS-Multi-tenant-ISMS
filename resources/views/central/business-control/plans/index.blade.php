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
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Business Control · Plan Management</h2>
    </x-slot>

    <div class="business-control-page mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ createOpen: @js($openCreate), editOpen: @js($openEditId) }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('central.business-control.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-100 dark:hover:bg-slate-800/80">Back to Business Control</a>
            <button type="button" @click="createOpen = true" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">
                + Create Plan
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

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($plans as $plan)
                @php
                    $flags = is_array($plan->feature_flags) ? $plan->feature_flags : [];
                    $marketingPoints = collect(preg_split('/\r\n|\r|\n/', (string) $plan->marketing_points))
                        ->map(fn (string $line): string => trim($line))
                        ->filter()
                        ->values();
                    $protectedPlan = in_array(strtolower((string) $plan->code), ['basic', 'pro'], true);
                @endphp

                <article class="relative overflow-hidden rounded-3xl border border-cyan-300/30 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.26),_rgba(15,23,42,0.96)_55%)] p-6 text-slate-100 shadow-[0_0_0_1px_rgba(34,211,238,0.12),0_18px_36px_rgba(6,10,25,0.45)]">
                    <div class="pointer-events-none absolute -right-12 -top-14 h-40 w-40 rounded-full bg-cyan-300/15 blur-3xl"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-3xl font-semibold">{{ $plan->name }}</h3>
                                <p class="mt-1 text-xs uppercase tracking-[0.18em] text-cyan-100/75">{{ $plan->marketing_tagline ?: 'Subscription Plan' }}</p>
                            </div>
                            <button type="button" @click="editOpen = {{ $plan->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-cyan-200/35 bg-cyan-400/10 text-cyan-100 hover:bg-cyan-400/20" aria-label="Edit {{ $plan->name }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a2.121 2.121 0 1 1 3 3L10.582 16.767a4.5 4.5 0 0 1-1.897 1.13L6 18.75l.853-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487ZM19.5 7.125 16.875 4.5" />
                                </svg>
                            </button>
                        </div>

                        @if ($plan->is_featured || $plan->badge_label)
                            <div class="mt-4 inline-flex rounded-full border border-cyan-200/35 bg-cyan-400/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-cyan-100">
                                {{ $plan->badge_label ?: 'Featured' }}
                            </div>
                        @endif

                        <div class="mt-5">
                            <p class="text-6xl font-semibold leading-none">${{ number_format((float) $plan->monthly_price, 0) }}<span class="ml-2 text-3xl font-medium text-cyan-100/80">/month</span></p>
                            <p class="mt-3 text-sm text-cyan-100/65">
                                Regular: <span class="line-through">${{ number_format((float) $plan->monthly_price * 12, 2) }}/year</span>
                            </p>
                            <p class="mt-1 text-2xl font-semibold">
                                ${{ number_format((float) $plan->yearly_price, 2) }} /year
                                <span class="text-lg font-medium text-emerald-300">(save {{ number_format((float) $plan->yearly_discount_percent, 2) }}%)</span>
                            </p>
                        </div>

                        <ul class="mt-6 space-y-2 text-sm">
                            @foreach ($marketingPoints as $point)
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-cyan-400/80 text-xs font-bold text-slate-900">✓</span>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-cyan-400/80 text-xs font-bold text-slate-900">✓</span>
                                <span>Users: {{ $plan->max_users ?? 'Unlimited' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-cyan-400/80 text-xs font-bold text-slate-900">✓</span>
                                <span>Teams: {{ $plan->max_teams ?? 'Unlimited' }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-cyan-400/80 text-xs font-bold text-slate-900">✓</span>
                                <span>Sports: {{ $plan->max_sports ?? 'Unlimited' }}</span>
                            </li>
                        </ul>

                        <div class="mt-6 flex items-center justify-between gap-2 text-xs">
                            <span class="rounded-full border px-2.5 py-1 {{ $plan->is_active ? 'border-emerald-300/40 bg-emerald-500/15 text-emerald-100' : 'border-slate-300/30 bg-slate-500/20 text-slate-200' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="rounded-full border px-2.5 py-1 {{ ($flags['analytics'] ?? false) ? 'border-cyan-300/40 bg-cyan-400/15 text-cyan-100' : 'border-slate-300/30 bg-slate-500/20 text-slate-200' }}">Analytics {{ ($flags['analytics'] ?? false) ? 'On' : 'Off' }}</span>
                            <span class="rounded-full border px-2.5 py-1 {{ ($flags['bracket'] ?? false) ? 'border-cyan-300/40 bg-cyan-400/15 text-cyan-100' : 'border-slate-300/30 bg-slate-500/20 text-slate-200' }}">Bracket {{ ($flags['bracket'] ?? false) ? 'On' : 'Off' }}</span>
                        </div>

                        <button type="button" @click="editOpen = {{ $plan->id }}" class="mt-7 w-full rounded-2xl border border-cyan-300/30 bg-cyan-400/20 px-4 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-400/30">
                            {{ $plan->cta_label ?: 'Manage Plan' }}
                        </button>

                        <div class="mt-5 rounded-2xl border border-cyan-200/20 bg-slate-950/35 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-cyan-100/75">Version History</p>

                            @if ($plan->versions->isEmpty())
                                <p class="mt-2 text-xs text-cyan-100/65">No versions yet.</p>
                            @else
                                <ul class="mt-2 space-y-1.5 text-xs text-cyan-50/90">
                                    @foreach ($plan->versions as $version)
                                        <li class="flex items-center justify-between gap-2 rounded-lg border border-cyan-200/15 bg-cyan-500/5 px-2.5 py-1.5">
                                            <span class="font-semibold">v{{ $version->version_number }}</span>
                                            <span class="truncate text-cyan-100/80">{{ $version->name }}</span>
                                            <span class="shrink-0 text-cyan-100/65">{{ $version->created_at?->format('M d, Y h:i A') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500 shadow-sm dark:border-white/10 dark:bg-slate-900/80 dark:text-slate-400">
                    No plans found yet.
                </div>
            @endforelse
        </div>

        <div x-show="createOpen" x-cloak class="fixed inset-0 z-40 overflow-y-auto p-4 sm:p-6" aria-modal="true" role="dialog">
            <div class="fixed inset-0 bg-slate-950/70" @click="createOpen = false"></div>
            <div class="relative mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-slate-900/95">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Create Plan</h3>
                    <button type="button" @click="createOpen = false" class="rounded-lg border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-100 dark:border-white/15 dark:text-slate-300 dark:hover:bg-slate-800">Close</button>
                </div>

                <form method="POST" action="{{ route('central.business-control.plans.store') }}" class="grid gap-3 md:grid-cols-3">
                    @csrf
                    <input type="hidden" name="_form" value="create">

                    <input type="text" name="code" value="{{ old('code') }}" placeholder="code" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="name" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <input type="number" step="1" min="1" max="9999" name="sort_order" value="{{ old('sort_order') }}" placeholder="sort order" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">

                    <input type="text" name="marketing_tagline" value="{{ old('marketing_tagline') }}" placeholder="tagline" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-2">
                    <input type="text" name="badge_label" value="{{ old('badge_label') }}" placeholder="badge label" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <input type="text" name="cta_label" value="{{ old('cta_label') }}" placeholder="button label" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">
                    <textarea name="marketing_points" rows="3" placeholder="Marketing points (one line per bullet)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">{{ old('marketing_points') }}</textarea>

                    <input type="number" step="0.01" min="0" name="monthly_price" value="{{ old('monthly_price') }}" placeholder="monthly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <input type="number" step="0.01" min="0" name="yearly_price" value="{{ old('yearly_price') }}" placeholder="yearly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                    <select name="is_active" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        <option value="1" @selected((string) old('is_active', '1') === '1')>Active</option>
                        <option value="0" @selected((string) old('is_active') === '0')>Inactive</option>
                    </select>

                    <input type="number" min="1" name="max_users" value="{{ old('max_users') }}" placeholder="max users (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <input type="number" min="1" name="max_teams" value="{{ old('max_teams') }}" placeholder="max teams (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                    <input type="number" min="1" name="max_sports" value="{{ old('max_sports') }}" placeholder="max sports (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">

                    <select name="is_featured" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">
                        <option value="0" @selected((string) old('is_featured', '0') === '0')>Standard Plan</option>
                        <option value="1" @selected((string) old('is_featured') === '1')>Featured Plan</option>
                    </select>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Feature Flags</p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="hidden" name="feature_flags[analytics]" value="0">
                                <input type="checkbox" name="feature_flags[analytics]" value="1" @checked((bool) old('feature_flags.analytics', false)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                Analytics Dashboard
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="hidden" name="feature_flags[bracket]" value="0">
                                <input type="checkbox" name="feature_flags[bracket]" value="1" @checked((bool) old('feature_flags.bracket', false)) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                Bracket Generator
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30 md:col-span-3">Create Plan</button>
                </form>
            </div>
        </div>

        @foreach ($plans as $plan)
            @php
                $flags = is_array($plan->feature_flags) ? $plan->feature_flags : [];
                $protectedPlan = in_array(strtolower((string) $plan->code), ['basic', 'pro'], true);
                $isEditingThisPlan = $oldForm === 'edit-'.$plan->id;
            @endphp

            <div x-show="editOpen === {{ $plan->id }}" x-cloak class="fixed inset-0 z-40 overflow-y-auto p-4 sm:p-6" aria-modal="true" role="dialog">
                <div class="fixed inset-0 bg-slate-950/70" @click="editOpen = null"></div>
                <div class="relative mx-auto max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-slate-900/95">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Edit {{ $plan->name }} ({{ strtoupper($plan->code) }})</h3>
                        <button type="button" @click="editOpen = null" class="rounded-lg border border-slate-300 px-2 py-1 text-slate-600 hover:bg-slate-100 dark:border-white/15 dark:text-slate-300 dark:hover:bg-slate-800">Close</button>
                    </div>

                    <form method="POST" action="{{ route('central.business-control.plans.update', $plan) }}" class="grid gap-3 md:grid-cols-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit-{{ $plan->id }}">

                        <input type="text" name="code" value="{{ $isEditingThisPlan ? old('code', $plan->code) : $plan->code }}" placeholder="code" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <input type="text" name="name" value="{{ $isEditingThisPlan ? old('name', $plan->name) : $plan->name }}" placeholder="name" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <input type="number" min="1" max="9999" name="sort_order" value="{{ $isEditingThisPlan ? old('sort_order', (string) $plan->sort_order) : $plan->sort_order }}" placeholder="sort order" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">

                        <input type="text" name="marketing_tagline" value="{{ $isEditingThisPlan ? old('marketing_tagline', $plan->marketing_tagline) : $plan->marketing_tagline }}" placeholder="tagline" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-2">
                        <input type="text" name="badge_label" value="{{ $isEditingThisPlan ? old('badge_label', $plan->badge_label) : $plan->badge_label }}" placeholder="badge label" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        <input type="text" name="cta_label" value="{{ $isEditingThisPlan ? old('cta_label', $plan->cta_label) : $plan->cta_label }}" placeholder="button label" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">
                        <textarea name="marketing_points" rows="3" placeholder="Marketing points (one line per bullet)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">{{ $isEditingThisPlan ? old('marketing_points', $plan->marketing_points) : $plan->marketing_points }}</textarea>

                        <input type="number" step="0.01" min="0" name="monthly_price" value="{{ $isEditingThisPlan ? old('monthly_price', (string) $plan->monthly_price) : $plan->monthly_price }}" placeholder="monthly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <input type="number" step="0.01" min="0" name="yearly_price" value="{{ $isEditingThisPlan ? old('yearly_price', (string) $plan->yearly_price) : $plan->yearly_price }}" placeholder="yearly price" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                        <select name="is_active" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                            <option value="1" @selected((string) ($isEditingThisPlan ? old('is_active', $plan->is_active ? '1' : '0') : ($plan->is_active ? '1' : '0')) === '1')>Active</option>
                            <option value="0" @selected((string) ($isEditingThisPlan ? old('is_active', $plan->is_active ? '1' : '0') : ($plan->is_active ? '1' : '0')) === '0')>Inactive</option>
                        </select>

                        <input type="number" min="1" name="max_users" value="{{ $isEditingThisPlan ? old('max_users', (string) $plan->max_users) : $plan->max_users }}" placeholder="max users (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        <input type="number" min="1" name="max_teams" value="{{ $isEditingThisPlan ? old('max_teams', (string) $plan->max_teams) : $plan->max_teams }}" placeholder="max teams (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">
                        <input type="number" min="1" name="max_sports" value="{{ $isEditingThisPlan ? old('max_sports', (string) $plan->max_sports) : $plan->max_sports }}" placeholder="max sports (blank = unlimited)" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100">

                        <select name="is_featured" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100 md:col-span-3">
                            <option value="0" @selected((string) ($isEditingThisPlan ? old('is_featured', $plan->is_featured ? '1' : '0') : ($plan->is_featured ? '1' : '0')) === '0')>Standard Plan</option>
                            <option value="1" @selected((string) ($isEditingThisPlan ? old('is_featured', $plan->is_featured ? '1' : '0') : ($plan->is_featured ? '1' : '0')) === '1')>Featured Plan</option>
                        </select>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 md:col-span-3 dark:border-white/10 dark:bg-slate-950/40">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">Feature Flags</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <input type="hidden" name="feature_flags[analytics]" value="0">
                                    <input type="checkbox" name="feature_flags[analytics]" value="1" @checked((bool) ($isEditingThisPlan ? old('feature_flags.analytics', $flags['analytics'] ?? false) : ($flags['analytics'] ?? false))) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                    Analytics Dashboard
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <input type="hidden" name="feature_flags[bracket]" value="0">
                                    <input type="checkbox" name="feature_flags[bracket]" value="1" @checked((bool) ($isEditingThisPlan ? old('feature_flags.bracket', $flags['bracket'] ?? false) : ($flags['bracket'] ?? false))) class="rounded border-slate-300 bg-white text-cyan-600 dark:border-white/20 dark:bg-slate-900 dark:text-cyan-500">
                                    Bracket Generator
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-3 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <button type="submit" class="rounded-xl border border-cyan-300 bg-cyan-100 px-4 py-2 text-sm font-semibold text-cyan-800 hover:bg-cyan-200 dark:border-cyan-300/40 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">Save Changes</button>
                                @if ($protectedPlan)
                                    <span class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-3 py-1 text-xs text-slate-700 dark:border-white/15 dark:bg-slate-950/70 dark:text-slate-300">Protected Plan</span>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if (! $protectedPlan)
                        <form method="POST" action="{{ route('central.business-control.plans.destroy', $plan) }}" class="mt-3" onsubmit="return confirm('Delete this plan? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-rose-300 bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-800 hover:bg-rose-200 dark:border-rose-300/30 dark:bg-rose-500/20 dark:text-rose-100 dark:hover:bg-rose-500/30">Delete Plan</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
