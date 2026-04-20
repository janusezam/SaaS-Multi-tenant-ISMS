<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePublicSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\University;
use App\Services\BusinessControl\PricingEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicSubscriptionController extends Controller
{
    public function landing(): View
    {
        return view('marketing.landing');
    }

    public function pricing(): View
    {
        $plans = Plan::query()->active()->orderBy('sort_order')->get();

        /** @var Collection<string, array<string, mixed>> $pricingByPlan */
        $pricingByPlan = $plans->mapWithKeys(function (Plan $plan): array {
            $pricingEngine = app(PricingEngine::class);

            return [
                (string) $plan->code => [
                    'monthly' => $pricingEngine->quote((string) $plan->code, 'monthly'),
                    'yearly' => $pricingEngine->quote((string) $plan->code, 'yearly'),
                ],
            ];
        });

        return view('marketing.pricing', [
            'plans' => $plans,
            'pricingByPlan' => $pricingByPlan,
        ]);
    }

    public function subscribe(StorePublicSubscriptionRequest $request, PricingEngine $pricingEngine): RedirectResponse
    {
        $validated = $request->validated();

        $quote = $pricingEngine->quote(
            (string) $validated['plan'],
            (string) $validated['billing_cycle'],
        );

        $university = new University([
            'id' => $validated['subdomain'],
            'name' => $validated['name'],
            'school_address' => $validated['school_address'],
            'tenant_admin_name' => $validated['tenant_admin_name'],
            'tenant_admin_email' => $validated['tenant_admin_email'],
            'plan' => $quote['plan']['code'],
            'status' => 'pending',
            'subscription_starts_at' => null,
            'expires_at' => null,
        ]);

        // Public signup should not provision tenant infrastructure until central approval.
        $university->setInternal('create_database', false);
        $university->assignObfuscatedDatabaseNameIfMissing();
        $university->save();

        $university->domains()->create([
            'domain' => $validated['tenant_domain'],
        ]);

        Subscription::query()->create([
            'tenant_id' => $university->id,
            'plan' => $quote['plan']['code'],
            'billing_cycle' => $quote['billing_cycle'],
            'base_price' => $quote['base_price'],
            'discount_amount' => $quote['discount_amount'],
            'final_price' => $quote['final_price'],
            'pricing_snapshot' => $quote,
            'status' => 'pending',
            'start_date' => null,
            'due_date' => null,
        ]);

        return redirect()
            ->route('public.pricing')
            ->with('status', 'Subscription request submitted. A central administrator will review and activate your tenant.');
    }
}
