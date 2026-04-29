<?php

namespace App\Policies;

use App\Models\Sport;
use App\Models\User;

class SportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'university_admin';
    }

    public function view(User $user, Sport $sport): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Sport $sport): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Sport $sport): bool
    {
        return $this->viewAny($user);
    }
}
