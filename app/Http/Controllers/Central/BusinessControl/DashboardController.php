<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\University;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeUniversities = University::query()->active()->with('subscription')->get();
        $billingDistribution = [
            'monthly' => (int) Subscription::query()->where('status', 'active')->where('billing_cycle', 'monthly')->count(),
            'yearly' => (int) Subscription::query()->where('status', 'active')->where('billing_cycle', 'yearly')->count(),
        ];

        return view('central.business-control.index', [
            'metrics' => [
                'activePlans' => Plan::query()->active()->count(),
                'activeCoupons' => Coupon::query()->active()->count(),
                'pendingUpgradeRequests' => SubscriptionUpgradeRequest::query()->where('status', 'pending')->count(),
                'activeUniversities' => $activeUniversities->count(),
                'basicSchools' => $activeUniversities->filter(fn (University $university): bool => $university->currentPlan() === 'basic')->count(),
                'proSchools' => $activeUniversities->filter(fn (University $university): bool => $university->currentPlan() === 'pro')->count(),
            ],
            'billingDistribution' => $billingDistribution,
            'recentPendingRequests' => SubscriptionUpgradeRequest::query()
                ->with('university')
                ->where('status', 'pending')
                ->latest()
                ->limit(6)
                ->get(),
            'activePlans' => Plan::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'activeCoupons' => Coupon::query()
                ->active()
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
