<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Coupon extends Model
{
    use CentralConnection;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'applies_to_plan',
        'starts_at',
        'expires_at',
        'usage_limit',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'bool',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isAvailableFor(string $planCode, ?Carbon $now = null): bool
    {
        $reference = $now ?? now();

        if (! $this->is_active) {
            return false;
        }

        if ($this->applies_to_plan !== null && $this->applies_to_plan !== $planCode) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isBefore($reference)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
