<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Edit Player</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            <form method="POST" action="{{ route('tenant.players.update', $player) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="team_id">Team</label>
                    <select id="team_id" name="team_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $player->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="student_id">Student ID</label>
                        <input id="student_id" name="student_id" value="{{ old('student_id', $player->student_id) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                        @error('student_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="position">Position</label>
                        <input id="position" name="position" value="{{ old('position', $player->position) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                        @error('position')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="player_user_id">Player (Existing Tenant Player User)</label>
                    <select id="player_user_id" name="player_user_id" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100">
                        <option value="">Select player user</option>
                        @foreach ($playerUsers as $playerUser)
                            <option value="{{ $playerUser->id }}" data-player-name="{{ $playerUser->name }}" data-player-email="{{ $playerUser->email }}" @selected(old('player_user_id', $selectedPlayerUserId) == $playerUser->id)>{{ $playerUser->name }} ({{ $playerUser->email }})</option>
                        @endforeach
                    </select>
                    @error('player_user_id')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">Selecting a player user auto-fills name and email fields below.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="first_name">First Name</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name', $player->first_name) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                        @error('first_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm text-slate-300" for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name', $player->last_name) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" required />
                        @error('last_name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm text-slate-300" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $player->email) }}" class="w-full rounded-xl border border-white/10 bg-slate-950/60 text-slate-100" />
                    @error('email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $player->is_active)) class="rounded border-white/10 bg-slate-950/60" />
                    Active
                </label>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Update</button>
                    <a href="{{ route('tenant.players.index') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const playerSelect = document.getElementById('player_user_id');
            const firstNameInput = document.getElementById('first_name');
            const lastNameInput = document.getElementById('last_name');
            const emailInput = document.getElementById('email');

            if (!playerSelect || !firstNameInput || !lastNameInput || !emailInput) {
                return;
            }

            const splitName = (fullName) => {
                const cleanName = (fullName || '').trim();

                if (cleanName.length === 0) {
                    return { first: '', last: '' };
                }

                const pieces = cleanName.split(/\s+/);
                const first = pieces.shift() || '';
                const last = pieces.join(' ');

                return { first, last: last || 'Player' };
            };

            const syncPlayerFields = () => {
                const option = playerSelect.options[playerSelect.selectedIndex];

                if (!option || option.value === '') {
                    return;
                }

                const parsed = splitName(option.getAttribute('data-player-name'));
                firstNameInput.value = parsed.first || firstNameInput.value;
                lastNameInput.value = parsed.last || lastNameInput.value;
                emailInput.value = option.getAttribute('data-player-email') || emailInput.value;
            };

            playerSelect.addEventListener('change', syncPlayerFields);
            syncPlayerFields();
        })();
    </script>
</x-app-layout>
