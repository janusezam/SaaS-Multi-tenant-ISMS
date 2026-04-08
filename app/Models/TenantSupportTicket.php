<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantSupportTicket extends Model
{
    use CentralConnection;

    protected $fillable = [
        'tenant_id',
        'tenant_name',
        'reported_by_user_id',
        'reported_by_name',
        'reported_by_email',
        'reported_by_role',
        'category',
        'subject',
        'message',
        'status',
        'central_note',
        'resolved_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }
}
