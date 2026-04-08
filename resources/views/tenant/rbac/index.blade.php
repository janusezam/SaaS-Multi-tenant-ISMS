<x-app-layout>
    @php
        $definitionsByModule = collect($definitions)->map(function (array $definition, string $permissionKey): array {
            return [
                'key' => $permissionKey,
                'module' => $definition['module'],
                'label' => $definition['label'],
                'description' => $definition['description'],
            ];
        })->groupBy('module');

        $roleLabels = [
            'sports_facilitator' => 'Sports Facilitator',
            'team_coach' => 'Team Coach',
            'student_player' => 'Student Player',
        ];

        $moduleNames = $definitionsByModule->keys()->values();
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-100">RBAC Matrix (Modular Access Control)</h2>
            <p class="mt-1 text-sm text-slate-300">Configure permissions by module and function for facilitator, coach, and player roles.</p>
        </div>
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

        <form method="POST" action="{{ route('tenant.rbac.update') }}" class="space-y-4" x-data="{
            selectedRole: '{{ $managedRoles[0] ?? 'team_coach' }}',
            selectedModule: '{{ $moduleNames->first() ?? '' }}',
            selectedAction: 'enable',
            toggleModule(module, role, checked) {
                const selector = `input[data-module='${module}'][data-role='${role}'][type='checkbox']`;
                document.querySelectorAll(selector).forEach((checkbox) => {
                    checkbox.checked = checked;
                });
            },
            applyModuleAccess() {
                if (!this.selectedModule || !this.selectedRole) {
                    return;
                }

                this.toggleModule(this.selectedModule, this.selectedRole, this.selectedAction === 'enable');
            }
        }">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-cyan-300/20 bg-slate-900/85 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-cyan-200">Add Modular Access</h3>
                <p class="mt-1 text-xs text-slate-400">Quickly apply module-wide access for one role, then review individual items before saving.</p>
                <div class="mt-4 grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="text-xs uppercase tracking-[0.12em] text-slate-400">Role</label>
                        <select x-model="selectedRole" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100">
                            @foreach ($managedRoles as $role)
                                <option value="{{ $role }}" style="color: #0f172a;">{{ $roleLabels[$role] ?? $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.12em] text-slate-400">Module</label>
                        <select x-model="selectedModule" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100">
                            @foreach ($moduleNames as $moduleName)
                                <option value="{{ $moduleName }}" style="color: #0f172a;">{{ $moduleName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.12em] text-slate-400">Action</label>
                        <select x-model="selectedAction" class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100">
                            <option value="enable" style="color: #0f172a;">Enable module permissions</option>
                            <option value="disable" style="color: #0f172a;">Disable module permissions</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="applyModuleAccess()" class="w-full rounded-xl border border-cyan-300/40 bg-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/30">
                            Apply Module Access
                        </button>
                    </div>
                </div>
            </section>

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
                        @foreach ($definitionsByModule as $moduleName => $moduleDefinitions)
                            <tr>
                                <td colspan="5" class="border-y border-white/10 bg-slate-950/50 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.16em] text-cyan-200">{{ $moduleName }}</p>
                                            <p class="mt-1 text-xs text-slate-400">{{ $moduleDefinitions->count() }} permission item(s)</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
                                            @foreach ($managedRoles as $role)
                                                <label class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2 py-1">
                                                    <input
                                                        type="checkbox"
                                                        class="h-3.5 w-3.5 rounded border-slate-500 bg-slate-800 text-cyan-400"
                                                        @change="toggleModule('{{ $moduleName }}', '{{ $role }}', $event.target.checked)"
                                                    >
                                                    <span>Select all {{ $roleLabels[$role] ?? $role }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            @foreach ($moduleDefinitions as $definition)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-semibold text-cyan-200">{{ $moduleName }}</p>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-slate-100">{{ $definition['label'] }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $definition['description'] }}</p>
                                    </td>

                                    @foreach ($managedRoles as $role)
                                        @php
                                            $isEnabled = (bool) ($matrix[$definition['key']][$role] ?? false);
                                        @endphp
                                        <td class="px-4 py-3 align-top">
                                            <label class="inline-flex items-center gap-2">
                                                <input type="hidden" name="permissions[{{ $definition['key'] }}][{{ $role }}]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[{{ $definition['key'] }}][{{ $role }}]"
                                                    value="1"
                                                    data-module="{{ $moduleName }}"
                                                    data-role="{{ $role }}"
                                                    class="h-4 w-4 rounded border-slate-500 bg-slate-800 text-cyan-400"
                                                    @checked($isEnabled)
                                                >
                                                <span class="text-xs text-slate-300">Enabled</span>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

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
