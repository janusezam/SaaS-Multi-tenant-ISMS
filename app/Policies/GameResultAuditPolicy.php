<?php

namespace App\Policies;

use App\Models\GameResultAudit;
use App\Models\User;

class GameResultAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['university_admin', 'sports_facilitator'], true);
    }

    public function view(User $user, GameResultAudit $gameResultAudit): bool
    {
        return $this->viewAny($user);
    }
}