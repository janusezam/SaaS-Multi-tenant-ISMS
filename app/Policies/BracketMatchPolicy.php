<?php

namespace App\Policies;

use App\Models\BracketMatch;
use App\Models\User;

class BracketMatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'university_admin';
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
