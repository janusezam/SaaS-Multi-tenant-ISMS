<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class SubscriptionUpgradeRequest extends Model
{
    use CentralConnection;

    protected $fillable = [
        'tenant_id',
        'requested_plan',
        'billing_cycle',
        'coupon_id',
        'coupon_code',
        'base_price',
        'discount_amount',
        'final_price',
        'requested_by_email',
        'requested_by_user_id',
        'status',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'processed_by_super_admin_id',
        'pricing_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'pricing_snapshot' => 'array',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class, 'tenant_id', 'id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'processed_by_super_admin_id');
    }
}
