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
        Plan::query()->create($request->validated());

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan created successfully.');
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()
            ->route('central.business-control.plans.index')
            ->with('status', 'Plan updated successfully.');
    }
}
