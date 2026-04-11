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
            'common.dashboard.view' => [
                'module' => 'Core Workspace',
                'label' => 'Open Role Dashboard',
                'description' => 'Access the role-based dashboard after login.',
            ],
            'common.standings.view' => [
                'module' => 'Core Workspace',
                'label' => 'View Standings',
                'description' => 'Open live standings and team rankings.',
            ],
            'common.profile.manage' => [
                'module' => 'Core Workspace',
                'label' => 'Manage Profile',
                'description' => 'Update own profile details and password.',
            ],
            'common.settings.view' => [
                'module' => 'Settings Management',
                'label' => 'Open Settings Workspace',
                'description' => 'Access the settings page and available tabs.',
            ],
            'common.settings.customization.manage' => [
                'module' => 'Settings Management',
                'label' => 'Manage Customization',
                'description' => 'Update theme colors and default mode preferences.',
            ],
            'common.settings.privacy.view' => [
                'module' => 'Settings Management',
                'label' => 'View Privacy Notice',
                'description' => 'Read the built-in system privacy notice.',
            ],
            'common.settings.support.manage' => [
                'module' => 'Settings Management',
                'label' => 'Manage Support Reports',
                'description' => 'Submit support issues and view recent support tickets.',
            ],
            'common.settings.updates.view' => [
                'module' => 'Settings Management',
                'label' => 'View System Updates',
                'description' => 'Open the tenant updates feed and release summaries.',
            ],
            'common.subscription.view' => [
                'module' => 'Core Workspace',
                'label' => 'View Subscription',
                'description' => 'Open plan limits, pricing, and upgrade information.',
            ],
            'facilitator.dashboard.view' => [
                'module' => 'Facilitator Operations',
                'label' => 'View Facilitator Dashboard',
                'description' => 'Open facilitator dashboard stream and summary cards.',
            ],
            'facilitator.venues.manage' => [
                'module' => 'Facilitator Operations',
                'label' => 'Manage Venues',
                'description' => 'Create, update, and delete venues.',
            ],
            'facilitator.games.manage' => [
                'module' => 'Facilitator Operations',
                'label' => 'Manage Schedules',
                'description' => 'Create, edit, and delete games.',
            ],
            'facilitator.results.audit' => [
                'module' => 'Facilitator Operations',
                'label' => 'View Result Audits',
                'description' => 'Open game audit history pages.',
            ],
            'facilitator.results.submit' => [
                'module' => 'Facilitator Operations',
                'label' => 'Submit Game Results',
                'description' => 'Update game outcomes and scores.',
            ],
            'coach.dashboard.view' => [
                'module' => 'Coach Operations',
                'label' => 'View Coach Dashboard',
                'description' => 'Open coach dashboard stream and summary cards.',
            ],
            'coach.schedules.view' => [
                'module' => 'Coach Operations',
                'label' => 'View Schedules Page',
                'description' => 'Open the coach schedules page.',
            ],
            'coach.team.view' => [
                'module' => 'Coach Operations',
                'label' => 'View My Team Page',
                'description' => 'Open the coach team management page.',
            ],
            'coach.lineup.manage' => [
                'module' => 'Coach Operations',
                'label' => 'Manage Team Lineup',
                'description' => 'Assign players and starters per match.',
            ],
            'coach.announcements.manage' => [
                'module' => 'Coach Operations',
                'label' => 'Manage Announcements',
                'description' => 'Publish team announcements.',
            ],
            'player.dashboard.view' => [
                'module' => 'Player Engagement',
                'label' => 'View Player Dashboard',
                'description' => 'Open player dashboard stream and summary cards.',
            ],
            'player.schedule.view' => [
                'module' => 'Player Engagement',
                'label' => 'View My Schedule Page',
                'description' => 'Open the player schedule page.',
            ],
            'player.attendance.respond' => [
                'module' => 'Player Engagement',
                'label' => 'Respond Attendance',
                'description' => 'Accept or decline assigned matches.',
            ],
            'player.roster.view' => [
                'module' => 'Player Engagement',
                'label' => 'View Team Roster',
                'description' => 'See player roster section.',
            ],
            'player.announcements.view' => [
                'module' => 'Player Engagement',
                'label' => 'View Announcements',
                'description' => 'See team announcements section.',
            ],
            'player.history.view' => [
                'module' => 'Player Engagement',
                'label' => 'View Match History',
                'description' => 'See personal match history section.',
            ],
        ];
    }

    /**
     * @return array<string, array{description: string, bullets: array<int, string>}>
     */
    public function moduleSummaries(): array
    {
        return [
            'Core Workspace' => [
                'description' => 'Shared pages used by facilitator, coach, and player roles.',
                'bullets' => [
                    'Dashboard entry and navigation',
                    'Standings and ranking visibility',
                    'Profile and password maintenance',
                    'Subscription and plan visibility',
                ],
            ],
            'Settings Management' => [
                'description' => 'Granular controls for settings tabs and support operations.',
                'bullets' => [
                    'Settings workspace visibility',
                    'Customization controls and privacy notice visibility',
                    'Support report operations',
                    'System update feed visibility',
                ],
            ],
            'Facilitator Operations' => [
                'description' => 'Operational controls for venues, schedules, and results.',
                'bullets' => [
                    'Venue and schedule lifecycle',
                    'Game result submission workflow',
                    'Result audit visibility',
                ],
            ],
            'Coach Operations' => [
                'description' => 'Team-centric controls for lineup and communication.',
                'bullets' => [
                    'Coach schedule overview',
                    'My Team workspace visibility',
                    'Lineup and starter assignment',
                    'Team announcements',
                ],
            ],
            'Player Engagement' => [
                'description' => 'Player-side participation and communication options.',
                'bullets' => [
                    'My schedule and dashboard views',
                    'Attendance response actions',
                    'Roster, announcement, and history visibility',
                ],
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
                'common.dashboard.view' => true,
                'common.standings.view' => true,
                'common.profile.manage' => true,
                'common.settings.view' => true,
                'common.settings.customization.manage' => true,
                'common.settings.privacy.view' => true,
                'common.settings.support.manage' => true,
                'common.settings.updates.view' => true,
                'common.subscription.view' => true,
                'facilitator.dashboard.view' => true,
                'facilitator.venues.manage' => true,
                'facilitator.games.manage' => true,
                'facilitator.results.audit' => true,
                'facilitator.results.submit' => true,
            ],
            'team_coach' => [
                'common.dashboard.view' => true,
                'common.standings.view' => true,
                'common.profile.manage' => true,
                'common.settings.view' => true,
                'common.settings.customization.manage' => true,
                'common.settings.privacy.view' => true,
                'common.settings.support.manage' => true,
                'common.settings.updates.view' => true,
                'common.subscription.view' => true,
                'coach.dashboard.view' => true,
                'coach.schedules.view' => true,
                'coach.team.view' => true,
                'coach.lineup.manage' => true,
                'coach.announcements.manage' => true,
            ],
            'student_player' => [
                'common.dashboard.view' => true,
                'common.standings.view' => true,
                'common.profile.manage' => true,
                'common.settings.view' => true,
                'common.settings.customization.manage' => true,
                'common.settings.privacy.view' => true,
                'common.settings.support.manage' => true,
                'common.settings.updates.view' => true,
                'common.subscription.view' => true,
                'player.dashboard.view' => true,
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
