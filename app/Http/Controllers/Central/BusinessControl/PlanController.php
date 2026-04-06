<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlanRequest;
use App\Http\Requests\Central\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.plans.index', [
            'plans' => Plan::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? $this->nextSortOrder();
        $payload['feature_flags'] = $payload['feature_flags'] ?? [
            'analytics' => false,
            'bracket' => false,
        ];
        $payload['yearly_discount_percent'] = $this->computeYearlyDiscountPercent(
            (float) $payload['monthly_price'],
            (float) $payload['yearly_price'],
        );

        if ((bool) ($payload['is_featured'] ?? false)) {
            $this->unsetFeaturedPlans();
        }

        Plan::query()->create($payload);

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan created successfully.');
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $payload = $request->validated();
        $payload['sort_order'] = $payload['sort_order'] ?? (int) $plan->sort_order;
        $payload['feature_flags'] = $payload['feature_flags'] ?? [
            'analytics' => false,
            'bracket' => false,
        ];
        $payload['yearly_discount_percent'] = $this->computeYearlyDiscountPercent(
            (float) $payload['monthly_price'],
            (float) $payload['yearly_price'],
        );

        if ((bool) ($payload['is_featured'] ?? false)) {
            $this->unsetFeaturedPlans($plan->id);
        }

        $plan->update($payload);

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if (in_array(strtolower((string) $plan->code), ['basic', 'pro'], true)) {
            return redirect()
                ->route('central.business-control.plans.index')
                ->with('status', 'Basic and Pro plans are protected and cannot be deleted.');
        }

        $planCode = (string) $plan->code;
        $isInUse = Subscription::query()->where('plan', $planCode)->exists()
            || SubscriptionUpgradeRequest::query()->where('requested_plan', $planCode)->exists();

        if ($isInUse) {
            return redirect()
                ->route('central.business-control.plans.index')
                ->with('status', 'Plan cannot be deleted because it is already used by subscriptions or upgrade requests.');
        }

        $plan->delete();

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan deleted successfully.');
    }

    private function computeYearlyDiscountPercent(float $monthlyPrice, float $yearlyPrice): float
    {
        if ($monthlyPrice <= 0) {
            return 0.0;
        }

        $annualMonthlyBaseline = $monthlyPrice * 12;
        $discountPercent = (1 - ($yearlyPrice / $annualMonthlyBaseline)) * 100;

        return round(max(0, $discountPercent), 2);
    }

    private function nextSortOrder(): int
    {
        $maxSortOrder = (int) (Plan::query()->max('sort_order') ?? 0);

        return $maxSortOrder > 0 ? $maxSortOrder + 10 : 10;
    }

    private function unsetFeaturedPlans(?int $exceptPlanId = null): void
    {
        Plan::query()
            ->when(
                $exceptPlanId !== null,
                fn ($query) => $query->whereKeyNot($exceptPlanId),
            )
            ->update(['is_featured' => false]);
    }
}
