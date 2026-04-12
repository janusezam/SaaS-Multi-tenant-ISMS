<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ApplyCampaignToRenewalsRequest;
use App\Http\Requests\Central\StorePromotionCampaignRequest;
use App\Http\Requests\Central\UpdatePromotionCampaignRequest;
use App\Models\CampaignVersion;
use App\Models\Plan;
use App\Models\PromotionCampaign;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromotionCampaignController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.campaigns.index', [
            'campaigns' => PromotionCampaign::query()
                ->with([
                    'versions' => fn ($query) => $query->orderByDesc('version_number')->limit(5),
                ])
                ->orderByDesc('discount_value')
                ->orderByDesc('starts_at')
                ->paginate(20),
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
            'activeSubscriptionCount' => Subscription::query()->where('status', 'active')->count(),
        ]);
    }

    public function store(StorePromotionCampaignRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $campaign = PromotionCampaign::query()->create($request->validated());
            $this->createCampaignVersionSnapshot($campaign->fresh(), (int) $request->user('super_admin')->id);
        });

        return redirect()
            ->route('central.business-control.campaigns.index')
            ->with('status', 'Promotion campaign created successfully.');
    }

    public function update(UpdatePromotionCampaignRequest $request, PromotionCampaign $campaign): RedirectResponse
    {
        DB::transaction(function () use ($request, $campaign): void {
            $campaign->update($request->validated());
            $this->createCampaignVersionSnapshot($campaign->fresh(), (int) $request->user('super_admin')->id);
        });

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

    private function createCampaignVersionSnapshot(PromotionCampaign $campaign, int $changedBySuperAdminId): CampaignVersion
    {
        $nextVersionNumber = ((int) $campaign->versions()->max('version_number')) + 1;

        return $campaign->versions()->create([
            'version_number' => $nextVersionNumber,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'discount_type' => $campaign->discount_type,
            'discount_value' => $campaign->discount_value,
            'target_plan_codes' => $campaign->target_plan_codes,
            'is_stackable_with_coupon' => (bool) ($campaign->is_stackable_with_coupon ?? false),
            'priority' => (int) ($campaign->priority ?? 100),
            'lifecycle_policy' => $campaign->lifecycle_policy,
            'starts_at' => $campaign->starts_at,
            'ends_at' => $campaign->ends_at,
            'description' => $campaign->description,
            'changed_by_super_admin_id' => $changedBySuperAdminId,
        ]);
    }
}
