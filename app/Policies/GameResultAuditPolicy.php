<?php

namespace App\Policies;

use App\Models\GameResultAudit;
use App\Models\User;
use App\Support\TenantPermissionMatrix;

class GameResultAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return app(TenantPermissionMatrix::class)->allows($user, 'facilitator.results.audit');
    }

    public function view(User $user, GameResultAudit $gameResultAudit): bool
    {
        return $this->viewAny($user);
    }
}
