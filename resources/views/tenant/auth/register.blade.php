<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-theme-static-auth>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">
        <title>Tenant Self Registration | ISMS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="isms-theme antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_12%_8%,rgba(16,185,129,0.22),transparent_35%),radial-gradient(circle_at_88%_0%,rgba(14,165,233,0.18),transparent_30%),linear-gradient(180deg,#03111f,#0b1224)] px-4 py-8 sm:px-6">
            <div class="mx-auto max-w-2xl rounded-3xl border border-white/15 bg-[#0f172a]/95 p-6 shadow-2xl shadow-slate-950/40 backdrop-blur sm:p-8">
                <h1 class="text-2xl font-semibold text-white">Request Tenant Account</h1>
                <p class="mt-2 text-sm text-white/70">Submit your details for approval by your tenant university admin.</p>

                <form method="POST" action="{{ route('tenant.register.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                    @csrf

                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-white/80">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-white/80">Email Address</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-sm font-medium text-white/80">Contact Number</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="role" class="block text-sm font-medium text-white/80">Requested Role</label>
                        <select id="role" name="role" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                            <option value="">Select role</option>
                            <option value="sports_facilitator" @selected(old('role') === 'sports_facilitator')>Sports Facilitator</option>
                            <option value="team_coach" @selected(old('role') === 'team_coach')>Team Coach</option>
                            <option value="student_player" @selected(old('role') === 'student_player')>Student Player</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-white/80">Password</label>
                        <input id="password" name="password" type="password" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-white/80">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full rounded-xl border border-white/20 bg-[#0b1224] px-4 py-3 text-white">
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/30 transition hover:bg-emerald-500">Submit for Approval</button>
                    </div>
                </form>

                <div class="mt-5 text-sm">
                    <a href="{{ route('tenant.login') }}" class="text-emerald-200 transition hover:text-emerald-100">Back to tenant login</a>
                </div>
            </div>
        </div>
    </body>
</html>
