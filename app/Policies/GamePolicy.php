<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use App\Support\TenantPermissionMatrix;

class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return app(TenantPermissionMatrix::class)->allows($user, 'facilitator.games.manage');
    }

    public function view(User $user, Game $game): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Game $game): bool
    {
        $permissionMatrix = app(TenantPermissionMatrix::class);

        return $permissionMatrix->allows($user, 'facilitator.games.manage')
            || $permissionMatrix->allows($user, 'facilitator.results.submit');
    }

    public function delete(User $user, Game $game): bool
    {
        return $this->viewAny($user);
    }
}
