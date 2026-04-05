<?php

namespace App\Support;

use App\Models\TenantRolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TenantPermissionMatrix
{
    /**
     * @return array<int, string>
     */
    public function managedRoles(): array
    {
        return [
            'sports_facilitator',
            'team_coach',
            'student_player',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function definitions(): array
    {
        return [
            'facilitator.venues.manage' => [
                'module' => 'Facilitator',
                'label' => 'Manage Venues',
                'description' => 'Create, update, and delete venues.',
            ],
            'facilitator.games.manage' => [
                'module' => 'Facilitator',
                'label' => 'Manage Schedules',
                'description' => 'Create, edit, and delete games.',
            ],
            'facilitator.results.audit' => [
                'module' => 'Facilitator',
                'label' => 'View Result Audits',
                'description' => 'Open game audit history pages.',
            ],
            'facilitator.results.submit' => [
                'module' => 'Facilitator',
                'label' => 'Submit Game Results',
                'description' => 'Update game outcomes and scores.',
            ],
            'coach.schedules.view' => [
                'module' => 'Coach',
                'label' => 'View Schedules Page',
                'description' => 'Open the coach schedules page.',
            ],
            'coach.team.view' => [
                'module' => 'Coach',
                'label' => 'View My Team Page',
                'description' => 'Open the coach team management page.',
            ],
            'coach.lineup.manage' => [
                'module' => 'Coach',
                'label' => 'Manage Team Lineup',
                'description' => 'Assign players and starters per match.',
            ],
            'coach.announcements.manage' => [
                'module' => 'Coach',
                'label' => 'Manage Announcements',
                'description' => 'Publish team announcements.',
            ],
            'player.schedule.view' => [
                'module' => 'Player',
                'label' => 'View My Schedule Page',
                'description' => 'Open the player schedule page.',
            ],
            'player.attendance.respond' => [
                'module' => 'Player',
                'label' => 'Respond Attendance',
                'description' => 'Accept or decline assigned matches.',
            ],
            'player.roster.view' => [
                'module' => 'Player',
                'label' => 'View Team Roster',
                'description' => 'See player roster section.',
            ],
            'player.announcements.view' => [
                'module' => 'Player',
                'label' => 'View Announcements',
                'description' => 'See team announcements section.',
            ],
            'player.history.view' => [
                'module' => 'Player',
                'label' => 'View Match History',
                'description' => 'See personal match history section.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function defaults(): array
    {
        return [
            'sports_facilitator' => [
                'facilitator.venues.manage' => true,
                'facilitator.games.manage' => true,
                'facilitator.results.audit' => true,
                'facilitator.results.submit' => true,
            ],
            'team_coach' => [
                'coach.schedules.view' => true,
                'coach.team.view' => true,
                'coach.lineup.manage' => true,
                'coach.announcements.manage' => true,
            ],
            'student_player' => [
                'player.schedule.view' => true,
                'player.attendance.respond' => true,
                'player.roster.view' => true,
                'player.announcements.view' => true,
                'player.history.view' => true,
            ],
        ];
    }

    public function allows(?User $user, string $permissionKey): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->hasTenantRole('university_admin')) {
            return true;
        }

        $role = User::normalizeTenantRole($user->role);

        if ($role === null || ! in_array($role, $this->managedRoles(), true)) {
            return false;
        }

        $definitions = $this->definitions();

        if (! array_key_exists($permissionKey, $definitions)) {
            return false;
        }

        $defaultValue = $this->defaults()[$role][$permissionKey] ?? false;

        if (! Schema::hasTable('tenant_role_permissions')) {
            return $defaultValue;
        }

        $row = TenantRolePermission::query()
            ->where('role', $role)
            ->where('permission_key', $permissionKey)
            ->first();

        if ($row === null) {
            return $defaultValue;
        }

        return (bool) $row->is_enabled;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function matrix(): array
    {
        $matrix = [];

        foreach (array_keys($this->definitions()) as $permissionKey) {
            foreach ($this->managedRoles() as $role) {
                $matrix[$permissionKey][$role] = $this->defaults()[$role][$permissionKey] ?? false;
            }
        }

        if (! Schema::hasTable('tenant_role_permissions')) {
            return $matrix;
        }

        $rows = TenantRolePermission::query()
            ->whereIn('role', $this->managedRoles())
            ->whereIn('permission_key', array_keys($this->definitions()))
            ->get();

        foreach ($rows as $row) {
            $matrix[$row->permission_key][$row->role] = (bool) $row->is_enabled;
        }

        return $matrix;
    }

    /**
     * @param  array<string, array<string, bool>>  $submittedMatrix
     */
    public function persist(array $submittedMatrix, ?int $updatedByUserId = null): void
    {
        if (! Schema::hasTable('tenant_role_permissions')) {
            return;
        }

        foreach (array_keys($this->definitions()) as $permissionKey) {
            foreach ($this->managedRoles() as $role) {
                $enabled = (bool) ($submittedMatrix[$permissionKey][$role] ?? false);

                TenantRolePermission::query()->updateOrCreate(
                    [
                        'role' => $role,
                        'permission_key' => $permissionKey,
                    ],
                    [
                        'is_enabled' => $enabled,
                        'updated_by_user_id' => $updatedByUserId,
                    ],
                );
            }
        }
    }
}
