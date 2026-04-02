<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ApproveUniversityRequest;
use App\Models\Coupon;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UpgradeRequestController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.upgrade-requests.index', [
            'pendingRequests' => SubscriptionUpgradeRequest::query()
                ->with(['university.subscription', 'coupon'])
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'recentlyProcessedRequests' => SubscriptionUpgradeRequest::query()
                ->with(['university.subscription', 'coupon', 'processedBy'])
                ->whereIn('status', ['approved', 'rejected'])
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function approve(ApproveUniversityRequest $request, SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return redirect()
                ->route('central.business-control.upgrade-requests.index')
                ->with('status', 'Upgrade request is no longer pending.');
        }

        $manualOverride = $request->validated('manual_price_override');

        DB::transaction(function () use ($upgradeRequest, $manualOverride): void {
            $subscription = Subscription::query()->firstOrNew([
                'tenant_id' => $upgradeRequest->tenant_id,
            ]);

            $startDate = now();
            $newDueDate = $upgradeRequest->billing_cycle === 'yearly'
                ? $startDate->copy()->addYear()->toDateString()
                : $startDate->copy()->addMonth()->toDateString();

            $finalPrice = $manualOverride !== null
                ? (float) $manualOverride
                : (float) $upgradeRequest->final_price;

            $subscription->fill([
                'plan' => $upgradeRequest->requested_plan,
                'billing_cycle' => $upgradeRequest->billing_cycle,
                'base_price' => $upgradeRequest->base_price,
                'discount_amount' => $upgradeRequest->discount_amount,
                'final_price' => $finalPrice,
                'coupon_id' => $upgradeRequest->coupon_id,
                'coupon_code' => $upgradeRequest->coupon_code,
                'pricing_snapshot' => $upgradeRequest->pricing_snapshot,
                'status' => 'active',
                'start_date' => $startDate->toDateString(),
                'due_date' => $newDueDate,
                'approved_at' => now(),
            ]);
            $subscription->save();

            University::query()->whereKey($upgradeRequest->tenant_id)->update([
                'plan' => $upgradeRequest->requested_plan,
                'status' => 'active',
                'subscription_starts_at' => $startDate,
                'expires_at' => $newDueDate,
            ]);

            $upgradeRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
                'processed_by_super_admin_id' => auth('super_admin')->id(),
                'final_price' => $finalPrice,
                'rejected_at' => null,
                'rejection_reason' => null,
            ]);

            if ($upgradeRequest->coupon_id !== null) {
                Coupon::query()->whereKey($upgradeRequest->coupon_id)->increment('used_count');
            }
        });

        return redirect()
            ->route('central.business-control.upgrade-requests.index')
            ->with('status', 'Upgrade request approved and tenant subscription updated.');
    }

    public function reject(SubscriptionUpgradeRequest $upgradeRequest): RedirectResponse
    {
        if ($upgradeRequest->status !== 'pending') {
            return redirect()
                ->route('central.business-control.upgrade-requests.index')
                ->with('status', 'Upgrade request is no longer pending.');
        }

        $upgradeRequest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'processed_by_super_admin_id' => auth('super_admin')->id(),
        ]);

        return redirect()
            ->route('central.business-control.upgrade-requests.index')
            ->with('status', 'Upgrade request rejected.');
    }
}
