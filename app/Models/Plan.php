<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use CentralConnection;

    protected $fillable = [
        'code',
        'name',
        'monthly_price',
        'yearly_price',
        'yearly_discount_percent',
        'feature_flags',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'yearly_discount_percent' => 'decimal:2',
            'feature_flags' => 'array',
            'is_active' => 'bool',
        ];
    }

    public function hasFeature(string $featureKey): bool
    {
        $flags = $this->feature_flags;

        if (! is_array($flags)) {
            return false;
        }

        return (bool) ($flags[$featureKey] ?? false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
