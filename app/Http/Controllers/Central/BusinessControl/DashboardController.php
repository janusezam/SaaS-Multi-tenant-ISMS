<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\University;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.index', [
            'metrics' => [
                'activePlans' => Plan::query()->active()->count(),
                'activeCoupons' => Coupon::query()->active()->count(),
                'pendingUpgradeRequests' => SubscriptionUpgradeRequest::query()->where('status', 'pending')->count(),
                'activeUniversities' => University::query()->active()->count(),
            ],
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
