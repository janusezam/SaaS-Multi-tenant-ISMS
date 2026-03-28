<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">Edit Tenant User</h2>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('tenant.users.update', $user) }}" class="space-y-6 rounded-2xl border border-white/10 bg-slate-900/85 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="text-sm font-medium text-slate-200">Full Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label for="email" class="text-sm font-medium text-slate-200">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="role" class="text-sm font-medium text-slate-200">Role</label>
                <select id="role" name="role" required class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                    <option value="sports_facilitator" @selected(old('role', $user->role) === 'sports_facilitator')>Sports Facilitator</option>
                    <option value="team_coach" @selected(old('role', $user->role) === 'team_coach')>Team Coach</option>
                    <option value="student_player" @selected(old('role', $user->role) === 'student_player')>Student Player</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div class="rounded-xl border border-white/10 bg-slate-950/40 p-4">
                <h3 class="text-sm font-semibold text-slate-100">Change Password (Optional)</h3>
                <p class="mt-1 text-xs text-slate-400">Leave blank to keep the current password.</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="text-sm font-medium text-slate-200">New Password</label>
                        <input id="password" name="password" type="password" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-slate-200">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full rounded-xl border border-white/10 bg-slate-950/60 px-4 py-3 text-slate-100 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">Save Changes</button>
                <a href="{{ route('tenant.users.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
