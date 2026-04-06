<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ApplyCampaignToRenewalsRequest;
use App\Http\Requests\Central\StorePromotionCampaignRequest;
use App\Http\Requests\Central\UpdatePromotionCampaignRequest;
use App\Models\Plan;
use App\Models\PromotionCampaign;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionCampaignController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.campaigns.index', [
            'campaigns' => PromotionCampaign::query()
                ->orderBy('priority')
                ->orderByDesc('starts_at')
                ->paginate(20),
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
            'activeSubscriptionCount' => Subscription::query()->where('status', 'active')->count(),
        ]);
    }

    public function store(StorePromotionCampaignRequest $request): RedirectResponse
    {
        PromotionCampaign::query()->create($request->validated());

        return redirect()
            ->route('central.business-control.campaigns.index')
            ->with('status', 'Promotion campaign created successfully.');
    }

    public function update(UpdatePromotionCampaignRequest $request, PromotionCampaign $campaign): RedirectResponse
    {
        $campaign->update($request->validated());

        return redirect()
            ->route('central.business-control.campaigns.index')
            ->with('status', 'Promotion campaign updated successfully.');
    }

    public function applyToRenewals(ApplyCampaignToRenewalsRequest $request, PromotionCampaign $campaign): RedirectResponse
    {
        $filters = $request->validated();

        $affectedCount = Subscription::query()
            ->where('status', (string) ($filters['status'] ?? 'active'))
            ->when(
                filled($filters['plan_code'] ?? null),
                fn ($query) => $query->where('plan', (string) $filters['plan_code']),
            )
            ->when(
                filled($filters['billing_cycle'] ?? null),
                fn ($query) => $query->where('billing_cycle', (string) $filters['billing_cycle']),
            )
            ->update([
                'next_renewal_campaign_id' => $campaign->id,
            ]);

        return redirect()
            ->route('central.business-control.campaigns.index')
            ->with('status', "Campaign queued for next renewal on {$affectedCount} subscription(s).");
    }
}
