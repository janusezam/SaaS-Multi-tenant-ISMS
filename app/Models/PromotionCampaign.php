<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class PromotionCampaign extends Model
{
    use CentralConnection;

    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'target_plan_codes' => 'array',
            'is_stackable_with_coupon' => 'bool',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForPlan(Builder $query, string $planCode): Builder
    {
        return $query->where(function (Builder $builder) use ($planCode): void {
            $builder
                ->whereNull('target_plan_codes')
                ->orWhereJsonContains('target_plan_codes', $planCode);
        });
    }

    public function scopeWithinWindow(Builder $query, ?Carbon $reference = null): Builder
    {
        $now = $reference ?? now();

        return $query
            ->where(function (Builder $builder) use ($now): void {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $builder) use ($now): void {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function isApplicableToPlan(string $planCode): bool
    {
        $targetPlanCodes = $this->target_plan_codes;

        if (! is_array($targetPlanCodes) || $targetPlanCodes === []) {
            return true;
        }

        return in_array($planCode, $targetPlanCodes, true);
    }
}
