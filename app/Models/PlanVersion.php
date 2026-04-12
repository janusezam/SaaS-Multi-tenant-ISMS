<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class PlanVersion extends Model
{
    use CentralConnection;

    protected $fillable = [
        'plan_id',
        'version_number',
        'code',
        'name',
        'marketing_tagline',
        'badge_label',
        'cta_label',
        'marketing_points',
        'monthly_price',
        'yearly_price',
        'yearly_discount_percent',
        'max_users',
        'max_teams',
        'max_sports',
        'feature_flags',
        'is_active',
        'is_featured',
        'sort_order',
        'changed_by_super_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'yearly_discount_percent' => 'decimal:2',
            'max_users' => 'integer',
            'max_teams' => 'integer',
            'max_sports' => 'integer',
            'feature_flags' => 'array',
            'is_active' => 'bool',
            'is_featured' => 'bool',
            'sort_order' => 'integer',
            'changed_by_super_admin_id' => 'integer',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
