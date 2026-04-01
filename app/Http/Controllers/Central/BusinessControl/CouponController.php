<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreCouponRequest;
use App\Http\Requests\Central\UpdateCouponRequest;
use App\Models\Coupon;
use App\Models\Plan;
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
        Coupon::query()->create($request->validated());

        return redirect()
            ->route('central.business-control.coupons.index')
            ->with('status', 'Coupon created successfully.');
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->validated());

        return redirect()
            ->route('central.business-control.coupons.index')
            ->with('status', 'Coupon updated successfully.');
    }
}
