<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreCouponRequest;
use App\Http\Requests\Central\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\SubscriptionUpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.coupons.index', [
            'coupons' => Coupon::query()->latest()->paginate(20),
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['name'] = (string) ($payload['name'] ?? $payload['code']);

        Coupon::query()->create($payload);

        return redirect()
            ->route('central.business-control.coupons.index')
            ->with('status', 'Coupon created successfully.');
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $payload = $request->validated();
        $payload['name'] = (string) ($payload['name'] ?? $payload['code']);

        $coupon->update($payload);

        return redirect()
            ->route('central.business-control.coupons.index')
            ->with('status', 'Coupon updated successfully.');
    }

    public function redemptions(Coupon $coupon): View
    {
        return view('central.business-control.coupons.redemptions', [
            'coupon' => $coupon,
            'redemptions' => SubscriptionUpgradeRequest::query()
                ->with('university')
                ->where('coupon_id', $coupon->id)
                ->where('status', 'approved')
                ->latest('approved_at')
                ->paginate(20),
        ]);
    }
}
