<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;
use App\Support\TenantPermissionMatrix;

class VenuePolicy
{
    public function viewAny(User $user): bool
    {
        return app(TenantPermissionMatrix::class)->allows($user, 'facilitator.venues.manage');
    }

    public function view(User $user, Venue $venue): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Venue $venue): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $this->viewAny($user);
    }
}
