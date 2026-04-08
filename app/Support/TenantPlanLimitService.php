<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\University;

class TenantPlanLimitService
{
    public function __construct(private readonly ?University $tenant) {}

    public static function fromCurrentTenant(): self
    {
        $tenant = tenant();

        return new self($tenant instanceof University ? $tenant : null);
    }

    public function limit(string $resource): ?int
    {
        $plan = $this->plan();

        if ($plan === null) {
            return null;
        }

        return $plan->limitFor($resource);
    }

    public function hasCapacity(string $resource, int $currentCount, int $increment = 1): bool
    {
        $limit = $this->limit($resource);

        if ($limit === null) {
            return true;
        }

        return ($currentCount + $increment) <= $limit;
    }

    public function limitReachedMessage(string $resource): string
    {
        $plan = $this->plan();

        if ($plan === null) {
            return 'Plan limit reached.';
        }

        $limit = $plan->limitFor($resource);
        $resourceLabel = Plan::resourceLabel($resource);
        $limitLabel = $limit === null ? 'unlimited' : (string) $limit;

        return sprintf(
            'Plan limit reached: %s allows up to %s %s. Upgrade your subscription to add more.',
            $plan->name,
            $limitLabel,
            $resourceLabel,
        );
    }

    private function plan(): ?Plan
    {
        return $this->tenant?->currentPlanModel();
    }
}
