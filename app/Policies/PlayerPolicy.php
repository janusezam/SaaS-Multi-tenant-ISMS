<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['university_admin', 'sports_facilitator'], true);
    }

    public function view(User $user, Player $player): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Player $player): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Player $player): bool
    {
        return $this->viewAny($user);
    }
}
