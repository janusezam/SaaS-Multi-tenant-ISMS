<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PreviewPricingRequest;
use App\Http\Requests\Tenant\StoreUpgradeRequest;
use App\Models\Plan;
use App\Models\Sport;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\BusinessControl\PricingEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function show(): View
    {
        $tenantId = (string) (tenant()?->id ?? '');
        $tenant = tenant();

        $subscription = Subscription::query()->where('tenant_id', $tenantId)->first();
        $pendingUpgradeRequest = SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $plans = Plan::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('code');

        $additionalPlans = $plans
            ->filter(fn (Plan $plan): bool => ! in_array((string) $plan->code, ['basic', 'pro'], true))
            ->values();

        $currentPlanCode = (string) ($subscription?->plan ?? $tenant?->currentPlan() ?? 'basic');
        $currentBillingCycle = (string) ($subscription?->billing_cycle ?? 'monthly');
        $effectivePrice = (float) ($subscription?->final_price ?? 0);
        $expiryDate = $subscription?->due_date ?? $tenant?->currentDueDate();
        $recommendedPlan = $plans
            ->first(fn (Plan $plan): bool => (string) $plan->code !== $currentPlanCode);
        $currentPlan = $plans->get($currentPlanCode);

        $resourceUsage = [
            'users' => Schema::hasTable('users') ? User::query()->count() : null,
            'teams' => Schema::hasTable('teams') ? Team::query()->count() : null,
            'sports' => Schema::hasTable('sports') ? Sport::query()->count() : null,
        ];

        $resourceLimits = [
            'users' => $currentPlan?->max_users,
            'teams' => $currentPlan?->max_teams,
            'sports' => $currentPlan?->max_sports,
        ];

        return view('tenant.subscription.show', [
            'subscription' => $subscription,
            'pendingUpgradeRequest' => $pendingUpgradeRequest,
            'basicPlan' => $plans->get('basic'),
            'proPlan' => $plans->get('pro'),
            'additionalPlans' => $additionalPlans,
            'recommendedPlan' => $recommendedPlan,
            'tenantName' => (string) ($tenant?->name ?? 'Tenant School'),
            'currentPlanCode' => $currentPlanCode,
            'currentPlan' => $currentPlan,
            'currentBillingCycle' => $currentBillingCycle,
            'effectivePrice' => $effectivePrice,
            'expiryDate' => $expiryDate,
            'canSubmitUpgradeRequest' => auth()->user()?->role === 'university_admin',
            'openUpgradeModal' => request()->boolean('openUpgrade'),
            'resourceUsage' => $resourceUsage,
            'resourceLimits' => $resourceLimits,
        ]);
    }

    public function preview(PreviewPricingRequest $request, PricingEngine $pricingEngine): JsonResponse
    {
        $quote = $pricingEngine->quote(
            (string) $request->validated('plan'),
            (string) $request->validated('billing_cycle'),
            $request->validated('coupon_code'),
        );

        return response()->json([
            'quote' => $quote,
            'pending' => $this->hasPendingUpgradeRequest(),
        ]);
    }

    public function submit(StoreUpgradeRequest $request, PricingEngine $pricingEngine): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if ($user?->role !== 'university_admin') {
            throw ValidationException::withMessages([
                'role' => 'Only university admins can submit upgrade requests.',
            ]);
        }

        $tenant = tenant();

        if ($tenant === null) {
            abort(404);
        }

        $validated = $request->validated();

        if ((string) ($validated['requested_plan'] ?? '') === $tenant->currentPlan()) {
            throw ValidationException::withMessages([
                'requested_plan' => 'Selected plan is already your current plan.',
            ]);
        }

        if ($this->hasPendingUpgradeRequest()) {
            throw ValidationException::withMessages([
                'request' => 'An upgrade request is already pending.',
            ]);
        }

        $quote = $pricingEngine->quote(
            (string) $validated['requested_plan'],
            (string) $validated['billing_cycle'],
            $validated['coupon_code'] ?? null,
        );

        $pendingRequest = SubscriptionUpgradeRequest::query()->create([
            'tenant_id' => (string) $tenant->id,
            'requested_plan' => (string) $quote['plan']['code'],
            'billing_cycle' => (string) $quote['billing_cycle'],
            'coupon_id' => $quote['coupon']['id'] ?? null,
            'coupon_code' => $quote['coupon']['code'] ?? null,
            'base_price' => $quote['base_price'],
            'discount_amount' => $quote['discount_amount'],
            'final_price' => $quote['final_price'],
            'requested_by_email' => (string) $user->email,
            'requested_by_user_id' => $user->id,
            'status' => 'pending',
            'pricing_snapshot' => $quote,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'pending',
                'request_id' => $pendingRequest->id,
            ]);
        }

        return redirect()
            ->route('tenant.subscription.show')
            ->with('status', 'Upgrade request submitted and is now pending central approval.');
    }

    private function hasPendingUpgradeRequest(): bool
    {
        $tenantId = (string) (tenant()?->id ?? '');

        return SubscriptionUpgradeRequest::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->exists();
    }
}
