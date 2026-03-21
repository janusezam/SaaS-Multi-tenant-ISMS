<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['university_admin', 'sports_facilitator'], true);
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
        return $this->viewAny($user);
    }

    public function delete(User $user, Game $game): bool
    {
        return $this->viewAny($user);
    }
}