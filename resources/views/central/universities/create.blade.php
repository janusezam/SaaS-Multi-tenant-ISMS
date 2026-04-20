<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Create School Tenant</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900/85">
            <form method="POST" action="{{ route('central.universities.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="name">School Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required />
                    @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="school_address">School Address</label>
                    <input id="school_address" name="school_address" value="{{ old('school_address') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required />
                    @error('school_address')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="tenant_admin_name">Admin Name</label>
                        <input id="tenant_admin_name" name="tenant_admin_name" value="{{ old('tenant_admin_name') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required />
                        @error('tenant_admin_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="tenant_admin_email">Admin Email</label>
                        <input id="tenant_admin_email" type="email" name="tenant_admin_email" value="{{ old('tenant_admin_email') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required />
                        @error('tenant_admin_email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>

                <p class="rounded-xl border border-cyan-300/30 bg-cyan-500/10 px-3 py-2 text-xs text-cyan-900 dark:border-cyan-300/20 dark:text-cyan-100">
                    Schools created here are activated immediately and the tenant admin invite is sent right away.
                </p>

                <div>
                    <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="subdomain">Subdomain</label>
                    <input id="subdomain" name="subdomain" value="{{ old('subdomain') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Example: north-campus produces north-campus.localhost</p>
                    @error('subdomain')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="plan">Plan</label>
                        <select id="plan" name="plan" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" required>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->code }}" @selected(old('plan', 'basic') === $plan->code)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        @error('plan')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="subscription_starts_at">Subscription Starts</label>
                        <input id="subscription_starts_at" type="date" name="subscription_starts_at" value="{{ old('subscription_starts_at') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" />
                        @error('subscription_starts_at')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm text-slate-700 dark:text-slate-300" for="expires_at">Subscription Expiry (optional)</label>
                        <input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 dark:border-white/10 dark:bg-slate-950/60 dark:text-slate-100" />
                        @error('expires_at')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/10 px-4 py-2 text-sm font-medium text-cyan-900 hover:bg-cyan-500/15 dark:bg-cyan-500/20 dark:text-cyan-100 dark:hover:bg-cyan-500/30">
                        Create School
                    </button>
                    <a href="{{ route('central.universities.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
