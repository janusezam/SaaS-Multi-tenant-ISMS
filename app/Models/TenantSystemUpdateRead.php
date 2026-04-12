<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantSystemUpdateRead extends Model
{
    use CentralConnection;

    protected $fillable = [
        'system_update_id',
        'tenant_id',
        'tenant_user_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'tenant_user_id' => 'integer',
            'read_at' => 'datetime',
        ];
    }

    public function systemUpdate(): BelongsTo
    {
        return $this->belongsTo(SystemUpdate::class);
    }
}
