<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant;

class University extends Tenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    protected $table = 'tenants';

    protected $fillable = [
        'id',
        'name',
        'school_address',
        'tenant_admin_name',
        'tenant_admin_email',
        'plan',
        'status',
        'subscription_starts_at',
        'expires_at',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'subscription_starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'school_address',
            'tenant_admin_name',
            'tenant_admin_email',
            'plan',
            'status',
            'subscription_starts_at',
            'expires_at',
            'created_at',
            'updated_at',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isFuture();
    }
}
