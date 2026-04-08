<x-app-layout>
    @php
        $mediaUrl = static function (?string $path): ?string {
            if ($path === null || trim($path) === '') {
                return null;
            }

            $normalized = str_replace('\\', '/', trim($path));

            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $normalized;
            }

            $normalized = ltrim($normalized, '/');
            $normalized = preg_replace('#^(public/)+#', '', $normalized) ?? $normalized;
            $normalized = preg_replace('#^(storage/)+#', '', $normalized) ?? $normalized;

            return tenant_asset($normalized);
        };

        $profileImageUrl = $mediaUrl($user?->profile_photo_path);
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-300/80">Tenant Workspace</p>
            <h2 class="text-2xl font-semibold text-slate-100">Profile</h2>
            <p class="mt-1 text-sm text-slate-300">Update profile photo and personal account details.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/15 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="isms-surface rounded-2xl p-6">
            <form method="POST" action="{{ route('tenant.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="grid gap-6 md:grid-cols-[220px_1fr]">
                    <div>
                        <p class="text-sm font-medium text-slate-200">Profile Photo</p>
                        <div class="mt-3 h-40 w-40 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/40">
                            @if ($profileImageUrl !== null)
                                <img src="{{ $profileImageUrl }}" alt="{{ $user?->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-4xl font-semibold text-cyan-200">{{ strtoupper(substr((string) ($user?->name ?? 'U'), 0, 1)) }}</div>
                            @endif
                        </div>

                        <div class="mt-3 space-y-2">
                            <input type="file" name="profile_photo" accept="image/*" class="block w-full text-xs text-slate-300 file:mr-3 file:rounded-lg file:border file:border-white/20 file:bg-white/10 file:px-3 file:py-2 file:text-xs file:text-slate-100 hover:file:bg-white/20">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                <input type="checkbox" name="remove_profile_photo" value="1" class="rounded border-white/20 bg-white/5 text-cyan-400 focus:ring-cyan-300/40">
                                Remove current photo
                            </label>
                            @error('profile_photo')
                                <p class="text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="name" class="text-sm font-medium text-slate-200">Full Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100">
                            @error('name')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="text-sm font-medium text-slate-200">Email</label>
                            <input id="email" type="email" value="{{ $user?->email }}" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2 text-sm text-slate-300" disabled>
                        </div>

                        <div>
                            <label for="role" class="text-sm font-medium text-slate-200">Role</label>
                            <input id="role" type="text" value="{{ str_replace('_', ' ', (string) $user?->role) }}" class="mt-2 w-full rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2 text-sm text-slate-300" disabled>
                        </div>

                        <div>
                            <label for="phone" class="text-sm font-medium text-slate-200">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user?->phone) }}" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="+63 900 000 0000">
                            @error('phone')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_password" class="text-sm font-medium text-slate-200">Current Password</label>
                            <input id="current_password" name="current_password" type="password" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" autocomplete="current-password">
                            @error('current_password')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="text-sm font-medium text-slate-200">New Password</label>
                            <input id="password" name="password" type="password" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" autocomplete="new-password">
                            @error('password')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="password_confirmation" class="text-sm font-medium text-slate-200">Confirm New Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" autocomplete="new-password">
                            @error('password_confirmation')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="bio" class="text-sm font-medium text-slate-200">Bio</label>
                            <textarea id="bio" name="bio" rows="5" class="mt-2 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100" placeholder="Short profile description for your tenant account.">{{ old('bio', $user?->bio) }}</textarea>
                            @error('bio')
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg border border-cyan-300/30 bg-cyan-500/20 px-4 py-2 text-sm font-semibold text-cyan-100 hover:bg-cyan-500/30">Save Profile</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
