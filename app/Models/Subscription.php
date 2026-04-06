<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Subscription extends Model
{
    use CentralConnection;

    protected $fillable = [
        'tenant_id',
        'plan',
        'billing_cycle',
        'base_price',
        'discount_amount',
        'final_price',
        'coupon_id',
        'next_renewal_campaign_id',
        'coupon_code',
        'pricing_snapshot',
        'start_date',
        'due_date',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'base_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'pricing_snapshot' => 'array',
            'approved_at' => 'datetime',
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

    public function nextRenewalCampaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'next_renewal_campaign_id');
    }
}
