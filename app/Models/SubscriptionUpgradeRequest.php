<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUpgradeRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'requested_plan',
        'requested_by_email',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'tenant_id', 'id');
    }
}
