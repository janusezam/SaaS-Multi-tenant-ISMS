<x-app-layout>
    @php
        $permissionMatrix = app(\App\Support\TenantPermissionMatrix::class);
        $settingsUser = Auth::user();
        $canManageCustomization = $permissionMatrix->allows($settingsUser, 'common.settings.customization.manage');
        $canViewPrivacy = $permissionMatrix->allows($settingsUser, 'common.settings.privacy.view');
        $canManageSupport = $permissionMatrix->allows($settingsUser, 'common.settings.support.manage');
        $canViewUpdates = $permissionMatrix->allows($settingsUser, 'common.settings.updates.view');
        $availableTabs = collect([
            $canManageCustomization ? 'customization' : null,
            $canViewPrivacy ? 'privacy' : null,
            $canManageSupport ? 'support' : null,
            $canViewUpdates ? 'updates' : null,
        ])->filter()->values()->all();
        $initialTab = $availableTabs[0] ?? 'none';
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-300/80">Tenant Workspace</p>
            <h2 class="text-2xl font-semibold text-slate-100">Settings</h2>
            <p class="mt-1 text-sm text-slate-300">Customization, privacy notice details, support reporting, and update visibility.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8" x-data="{ tab: @js($initialTab) }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/15 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if (! empty($availableTabs))
            <div class="isms-surface rounded-2xl p-2">
                <div class="grid gap-2 sm:grid-cols-4">
                    @if ($canManageCustomization)
                        <button type="button" @click="tab = 'customization'" class="rounded-xl px-4 py-2 text-sm font-medium transition" :class="tab === 'customization' ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'border border-transparent text-slate-300 hover:bg-white/10'">Customization</button>
                    @endif
                    @if ($canViewPrivacy)
                        <button type="button" @click="tab = 'privacy'" class="rounded-xl px-4 py-2 text-sm font-medium transition" :class="tab === 'privacy' ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'border border-transparent text-slate-300 hover:bg-white/10'">Privacy Notice</button>
                    @endif
                    @if ($canManageSupport)
                        <button type="button" @click="tab = 'support'" class="rounded-xl px-4 py-2 text-sm font-medium transition" :class="tab === 'support' ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'border border-transparent text-slate-300 hover:bg-white/10'">Support</button>
                    @endif
                    @if ($canViewUpdates)
                        <button type="button" @click="tab = 'updates'" class="rounded-xl px-4 py-2 text-sm font-medium transition" :class="tab === 'updates' ? 'bg-cyan-500/20 text-cyan-100 border border-cyan-300/30' : 'border border-transparent text-slate-300 hover:bg-white/10'">Updates</button>
                    @endif
                </div>
            </div>
        @else
            <div class="isms-surface rounded-2xl p-6">
                <p class="text-sm text-slate-300">No settings modules are currently enabled for your role. Contact your University Admin.</p>
            </div>
        @endif

        @if ($canManageCustomization)
            <section x-show="tab === 'customization'" x-cloak class="isms-surface rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-slate-100">Theme and Brand Colors</h3>
            <p class="mt-1 text-sm text-slate-300">Control your tenant visual identity with primary and secondary colors plus default theme mode.</p>

            <form method="POST" action="{{ route('tenant.settings.update') }}" class="mt-6 grid gap-5 lg:grid-cols-3">
                @csrf
                @method('PATCH')

                <div class="lg:col-span-3">
                    <label class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">
                        <input type="hidden" name="use_custom_theme" value="0">
                        <input type="checkbox" name="use_custom_theme" value="1" class="mt-1 h-4 w-4 rounded border-white/20 bg-transparent text-cyan-500 focus:ring-cyan-400" @checked((bool) old('use_custom_theme', $customization['use_custom_theme']))>
                        <span>
                            <span class="font-semibold text-slate-100">Apply Custom Theme</span>
                            <span class="mt-1 block text-xs text-slate-300">When off, your tenant stays on the default ISMS light/dark colors (your chosen colors are saved but not applied). When on, the whole tenant UI uses the saved brand palette (backgrounds, cards, sidebar/header accents).</span>
                        </span>
                    </label>
                    @error('use_custom_theme')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand_primary_color" class="text-sm font-medium text-slate-200">Primary Color</label>
                    <div class="mt-2 flex items-center gap-3">
                        <input id="brand_primary_color" type="color" value="{{ old('brand_primary_color', $customization['brand_primary_color']) }}" oninput="this.nextElementSibling.value=this.value" class="h-10 w-14 rounded border border-white/20 bg-transparent p-1">
                        <input type="text" name="brand_primary_color" value="{{ old('brand_primary_color', $customization['brand_primary_color']) }}" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="#06b6d4">
                    </div>
                    @error('brand_primary_color')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="brand_secondary_color" class="text-sm font-medium text-slate-200">Secondary Color</label>
                    <div class="mt-2 flex items-center gap-3">
                        <input id="brand_secondary_color" type="color" value="{{ old('brand_secondary_color', $customization['brand_secondary_color']) }}" oninput="this.nextElementSibling.value=this.value" class="h-10 w-14 rounded border border-white/20 bg-transparent p-1">
                        <input type="text" name="brand_secondary_color" value="{{ old('brand_secondary_color', $customization['brand_secondary_color']) }}" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="#6366f1">
                    </div>
                    @error('brand_secondary_color')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="theme_preference" class="text-sm font-medium text-slate-200">Default Theme Mode</label>
                    <select id="theme_preference" name="theme_preference" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                        <option value="system" @selected(old('theme_preference', $customization['theme_preference']) === 'system')>System</option>
                        <option value="light" @selected(old('theme_preference', $customization['theme_preference']) === 'light')>Light</option>
                        <option value="dark" @selected(old('theme_preference', $customization['theme_preference']) === 'dark')>Dark</option>
                    </select>
                    @error('theme_preference')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-lg border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-500/30">Save Settings</button>
                </div>
            </form>
            </section>
        @endif

        @if ($canViewPrivacy)
            <section x-show="tab === 'privacy'" x-cloak class="isms-surface rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-slate-100">{{ $privacyNotice['title'] ?? 'System Privacy Notice' }}</h3>
            <p class="mt-1 text-sm text-slate-300">{{ $privacyNoticeSummary }}</p>

            <div class="mt-4 space-y-3">
                @foreach ($privacyNoticeSections as $section)
                    <article class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                        <h4 class="text-sm font-semibold text-cyan-100">{{ $section['heading'] }}</h4>
                        <p class="mt-1 text-sm leading-6 text-slate-200">{{ $section['content'] }}</p>
                    </article>
                @endforeach
            </div>
            </section>
        @endif

        @if ($canManageSupport)
            <section x-show="tab === 'support'" x-cloak class="space-y-6">
            <div class="isms-surface rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-100">Report an Issue to Central Support</h3>
                <p class="mt-1 text-sm text-slate-300">Reports are delivered to super admins in central Business Control.</p>

                <form method="POST" action="{{ route('tenant.settings.support.store') }}" class="mt-6 grid gap-4">
                    @csrf

                    <div>
                        <label for="category" class="text-sm font-medium text-slate-200">Issue Category</label>
                        <select id="category" name="category" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-100">
                            <option value="bug" @selected(old('category') === 'bug')>Bug</option>
                            <option value="access" @selected(old('category') === 'access')>Access / Permission</option>
                            <option value="billing" @selected(old('category') === 'billing')>Billing / Subscription</option>
                            <option value="other" @selected(old('category') === 'other')>Other</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="text-sm font-medium text-slate-200">Subject</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="Short summary of your issue">
                        @error('subject')
                            <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="text-sm font-medium text-slate-200">Issue Details</label>
                        <textarea id="message" name="message" rows="6" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="Include steps to reproduce and expected behavior.">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-500/30">Submit to Central Support</button>
                    </div>
                </form>
            </div>

            <div class="isms-surface rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-slate-100">Recent Support Reports</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($supportTickets as $ticket)
                        <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-100">{{ $ticket->subject }}</p>
                                <span class="rounded-full border border-white/20 px-2 py-0.5 text-[11px] uppercase tracking-[0.15em] text-slate-300">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-400">{{ strtoupper($ticket->category) }} · {{ $ticket->created_at?->diffForHumans() }}</p>
                            @if (!empty($ticket->central_note))
                                <p class="mt-2 text-xs text-emerald-200">Central note: {{ $ticket->central_note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No reports submitted yet.</p>
                    @endforelse
                </div>
            </div>
            </section>
        @endif

        @if ($canViewUpdates)
            <section x-show="tab === 'updates'" x-cloak class="isms-surface rounded-2xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">System Updates</h3>
                    <p class="mt-1 text-sm text-slate-300">Current tenant app version: <span class="font-semibold text-cyan-200">{{ $tenantVersion }}</span></p>
                </div>
                <div class="rounded-xl border border-amber-300/30 bg-amber-500/15 px-3 py-2 text-xs text-amber-100">
                    Updates published by central admins appear here automatically for all tenants.
                </div>
            </div>

            <div class="mt-5 rounded-xl border border-white/10 bg-slate-950/40 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-100">Latest GitHub Release</p>
                        @if (!empty($latestRelease['tag']))
                            <p class="mt-1 text-xs text-slate-400">Version: <span class="font-semibold text-cyan-200">{{ $latestRelease['tag'] }}</span>
                                @if (!empty($latestRelease['published_at']))
                                    · Published {{ \Illuminate\Support\Carbon::parse($latestRelease['published_at'])->diffForHumans() }}
                                @endif
                            </p>
                        @else
                            <p class="mt-1 text-xs text-slate-400">Unable to load latest release right now.</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if (!empty($latestRelease['html_url']))
                            <a href="{{ $latestRelease['html_url'] }}" target="_blank" rel="noopener" class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10">View on GitHub</a>
                        @endif

                        @if (($settingsUser?->role ?? null) === 'university_admin')
                            @if (!empty($selfUpdateInProgress))
                                <span class="rounded-lg border border-amber-300/30 bg-amber-500/15 px-3 py-2 text-xs font-semibold text-amber-100">Update in progress</span>
                            @elseif (!empty($updateAvailable))
                                <form method="POST" action="{{ route('tenant.settings.self-update') }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg border border-cyan-300/30 bg-cyan-500/20 px-3 py-2 text-xs font-semibold text-cyan-100 hover:bg-cyan-500/30">Update now</button>
                                </form>
                            @else
                                <span class="rounded-lg border border-emerald-300/30 bg-emerald-500/15 px-3 py-2 text-xs font-semibold text-emerald-100">Up to date</span>
                            @endif
                        @endif
                    </div>
                </div>

                @if (!empty($latestRelease['body']))
                    <div class="mt-3 rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-300">What’s new</p>
                        <div class="prose prose-invert mt-2 max-w-none text-sm text-slate-200">
                            {!! nl2br(e($latestRelease['body'])) !!}
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($systemUpdates as $update)
                    @php
                        $isRead = in_array($update->id, $readUpdateIds ?? [], true);
                    @endphp
                    <article class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-100">{{ $update->title }}</p>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.14em] {{ $isRead ? 'border-emerald-300/30 bg-emerald-500/15 text-emerald-100' : 'border-amber-300/30 bg-amber-500/15 text-amber-100' }}">
                                    {{ $isRead ? 'Read' : 'New' }}
                                </span>
                                <p class="text-xs text-slate-400">{{ $update->published_at?->format('M d, Y h:i A') ?? $update->created_at?->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-cyan-200">Version: {{ $update->version ?? 'N/A' }} · Source: {{ strtoupper($update->source) }}</p>
                        @if (!empty($update->summary))
                            <p class="mt-2 text-sm text-slate-300">{{ $update->summary }}</p>
                        @endif

                        @if (! $isRead)
                            <form method="POST" action="{{ route('tenant.settings.updates.read', $update) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="rounded-lg border border-cyan-300/30 bg-cyan-500/20 px-3 py-1.5 text-xs font-semibold text-cyan-100 hover:bg-cyan-500/30">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p class="text-sm text-slate-400">No published updates yet.</p>
                @endforelse
            </div>
            </section>
        @endif
    </div>
</x-app-layout>
