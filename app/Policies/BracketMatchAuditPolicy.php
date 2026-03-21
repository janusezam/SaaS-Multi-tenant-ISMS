<?php

namespace App\Policies;

use App\Models\BracketMatchAudit;
use App\Models\User;

class BracketMatchAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['university_admin', 'sports_facilitator'], true);
    }

    public function view(User $user, BracketMatchAudit $bracketMatchAudit): bool
    {
        return $this->viewAny($user);
    }
}
