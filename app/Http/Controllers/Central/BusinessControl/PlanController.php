<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePlanRequest;
use App\Http\Requests\Central\UpdatePlanRequest;
use App\Models\Plan;
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
        $payload['feature_flags'] = $payload['feature_flags'] ?? [
            'analytics' => false,
            'bracket' => false,
        ];
        $payload['yearly_discount_percent'] = $this->computeYearlyDiscountPercent(
            (float) $payload['monthly_price'],
            (float) $payload['yearly_price'],
        );

        Plan::query()->create($payload);

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan created successfully.');
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $payload = $request->validated();
        $payload['feature_flags'] = $payload['feature_flags'] ?? [
            'analytics' => false,
            'bracket' => false,
        ];
        $payload['yearly_discount_percent'] = $this->computeYearlyDiscountPercent(
            (float) $payload['monthly_price'],
            (float) $payload['yearly_price'],
        );

        $plan->update($payload);

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan updated successfully.');
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
}
