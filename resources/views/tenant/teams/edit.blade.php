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
    @endphp

    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Edit Team</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            <form method="POST" action="{{ route('tenant.teams.update', $team) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="sport_id">Sport</label>
                    <select id="sport_id" name="sport_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
                        @foreach ($sports as $sport)
                            <option value="{{ $sport->id }}" @selected(old('sport_id', $team->sport_id) == $sport->id)>{{ $sport->name }}</option>
                        @endforeach
                    </select>
                    @error('sport_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="name">Team Name</label>
                    <input id="name" name="name" value="{{ old('name', $team->name) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                    @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-3">
                    <label class="mb-2 block text-sm text-slate-300" for="logo">Team Logo</label>
                    @php
                        $teamLogoUrl = $mediaUrl($team->logo_path);
                    @endphp
                    @if ($teamLogoUrl !== null)
                        <img src="{{ $teamLogoUrl }}" alt="{{ $team->name }}" class="h-28 w-28 rounded-xl border border-white/10 object-cover" />
                    @endif
                    <input id="logo" type="file" name="logo" accept="image/*" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-200 file:mr-3 file:rounded-lg file:border file:border-white/15 file:bg-white/10 file:px-3 file:py-2 file:text-xs file:text-slate-100" />
                    @error('logo')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    @if (! empty($team->logo_path))
                        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-white/10 bg-slate-950/60" />
                            Remove current logo
                        </label>
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="coach_user_id">Coach (Existing Tenant Coach)</label>
                    <select id="coach_user_id" name="coach_user_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100">
                        <option value="">Select coach</option>
                        @foreach ($coaches as $coach)
                            <option value="{{ $coach->id }}" data-coach-name="{{ $coach->name }}" data-coach-email="{{ $coach->email }}" @selected(old('coach_user_id', $selectedCoachUserId) == $coach->id)>{{ $coach->name }} ({{ $coach->email }})</option>
                        @endforeach
                    </select>
                    @error('coach_user_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">Choosing a coach auto-fills name and email from the selected user.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="coach_name">Coach Name</label>
                        <input id="coach_name" name="coach_name" value="{{ old('coach_name', $team->coach_name) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                        @error('coach_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="coach_email">Coach Email</label>
                        <input id="coach_email" type="email" name="coach_email" value="{{ old('coach_email', $team->coach_email) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                        @error('coach_email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="division">Division</label>
                    <input id="division" name="division" value="{{ old('division', $team->division) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                    @error('division')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $team->is_active)) class="rounded border-white/10 bg-slate-950/60" />
                    Active
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Update</button>
                    <a href="{{ route('tenant.teams.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const coachSelect = document.getElementById('coach_user_id');
            const coachName = document.getElementById('coach_name');
            const coachEmail = document.getElementById('coach_email');

            if (!coachSelect || !coachName || !coachEmail) {
                return;
            }

            const syncCoachFields = () => {
                const option = coachSelect.options[coachSelect.selectedIndex];

                if (!option || option.value === '') {
                    return;
                }

                coachName.value = option.getAttribute('data-coach-name') || coachName.value;
                coachEmail.value = option.getAttribute('data-coach-email') || coachEmail.value;
            };

            coachSelect.addEventListener('change', syncCoachFields);
            syncCoachFields();
        })();
    </script>
</x-app-layout>
