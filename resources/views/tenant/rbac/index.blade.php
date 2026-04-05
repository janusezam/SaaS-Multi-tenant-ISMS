<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-slate-100">RBAC Matrix (Roles & Access)</h2>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-300/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-300/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-2xl border border-cyan-300/25 bg-slate-900/85 p-6 text-slate-200">
            <p class="text-sm text-cyan-200">Enable or disable feature access by role. Changes apply tenant-wide for facilitators, coaches, and players.</p>
            <p class="mt-2 text-xs text-slate-400">University Admin always keeps full tenant access regardless of toggles.</p>
        </div>

        <form method="POST" action="{{ route('tenant.rbac.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/85">
                <table class="min-w-full divide-y divide-white/10 text-sm text-slate-200">
                    <thead class="bg-white/5 text-xs uppercase tracking-[0.12em] text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Module</th>
                            <th class="px-4 py-3 text-left font-medium">Permission</th>
                            <th class="px-4 py-3 text-left font-medium">Sports Facilitator</th>
                            <th class="px-4 py-3 text-left font-medium">Team Coach</th>
                            <th class="px-4 py-3 text-left font-medium">Student Player</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($definitions as $permissionKey => $definition)
                            <tr>
                                <td class="px-4 py-3 align-top">
                                    <p class="font-semibold text-cyan-200">{{ $definition['module'] }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <p class="font-medium text-slate-100">{{ $definition['label'] }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $definition['description'] }}</p>
                                </td>

                                @foreach ($managedRoles as $role)
                                    @php
                                        $isEnabled = (bool) ($matrix[$permissionKey][$role] ?? false);
                                    @endphp
                                    <td class="px-4 py-3 align-top">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="hidden" name="permissions[{{ $permissionKey }}][{{ $role }}]" value="0">
                                            <input
                                                type="checkbox"
                                                name="permissions[{{ $permissionKey }}][{{ $role }}]"
                                                value="1"
                                                class="h-4 w-4 rounded border-slate-500 bg-slate-800 text-cyan-400"
                                                @checked($isEnabled)
                                            >
                                            <span class="text-xs text-slate-300">Enabled</span>
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">
                    Save RBAC Matrix
                </button>
                <a href="{{ route('tenant.users.index') }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/10">
                    Back to Users
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
