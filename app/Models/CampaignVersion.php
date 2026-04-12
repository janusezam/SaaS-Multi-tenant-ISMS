<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class CampaignVersion extends Model
{
    use CentralConnection;

    protected $fillable = [
        'promotion_campaign_id',
        'version_number',
        'name',
        'status',
        'discount_type',
        'discount_value',
        'target_plan_codes',
        'is_stackable_with_coupon',
        'priority',
        'lifecycle_policy',
        'starts_at',
        'ends_at',
        'description',
        'changed_by_super_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'discount_value' => 'decimal:2',
            'target_plan_codes' => 'array',
            'is_stackable_with_coupon' => 'bool',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'changed_by_super_admin_id' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromotionCampaign::class, 'promotion_campaign_id');
    }
}
