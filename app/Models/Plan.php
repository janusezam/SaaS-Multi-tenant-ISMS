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
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'yearly_discount_percent' => 'decimal:2',
            'max_users' => 'integer',
            'max_teams' => 'integer',
            'max_sports' => 'integer',
            'feature_flags' => 'array',
            'is_active' => 'bool',
            'is_featured' => 'bool',
        ];
    }

    public function limitFor(string $resource): ?int
    {
        return match ($resource) {
            'users' => $this->max_users,
            'teams' => $this->max_teams,
            'sports' => $this->max_sports,
            default => null,
        };
    }

    public function isUnlimited(string $resource): bool
    {
        return $this->limitFor($resource) === null;
    }

    public static function resourceLabel(string $resource): string
    {
        return match ($resource) {
            'users' => 'users',
            'teams' => 'teams',
            'sports' => 'sports',
            default => $resource,
        };
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
