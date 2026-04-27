<?php

namespace App\Policies;

use App\Models\BracketMatch;
use App\Models\User;

class BracketMatchPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['university_admin', 'sports_facilitator'], true);
    }

    public function view(User $user, BracketMatch $bracketMatch): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, BracketMatch $bracketMatch): bool
    {
        return $this->viewAny($user);
    }
}
