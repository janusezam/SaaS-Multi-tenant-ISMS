<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
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

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'id');
    }

    public function currentPlan(): string
    {
        return (string) ($this->subscription?->plan ?? $this->plan ?? 'basic');
    }

    public function currentPlanModel(): ?Plan
    {
        return Plan::query()->where('code', $this->currentPlan())->first();
    }

    public function currentStatus(): string
    {
        return (string) ($this->subscription?->status ?? $this->status ?? 'pending');
    }

    public function currentDueDate(): ?Carbon
    {
        return $this->subscription?->due_date ?? $this->expires_at;
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->currentStatus() !== 'active') {
            return false;
        }

        $dueDate = $this->currentDueDate();

        if ($dueDate === null) {
            return true;
        }

        return $dueDate->endOfDay()->isFuture();
    }

    public function hasFeature(string $featureKey): bool
    {
        $plan = $this->currentPlanModel();

        if ($plan === null) {
            return false;
        }

        return $plan->hasFeature($featureKey);
    }

    public function limitFor(string $resource): ?int
    {
        $plan = $this->currentPlanModel();

        if ($plan === null) {
            return null;
        }

        return $plan->limitFor($resource);
    }
}
