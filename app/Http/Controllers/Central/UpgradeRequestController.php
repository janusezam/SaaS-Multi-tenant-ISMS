<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpgradeRequestController extends Controller
{
    public function store(Request $request): View
    {
        $tenantId = (string) $request->query('tenant');
        $requestedPlan = (string) $request->query('plan', 'pro');
        $email = (string) $request->query('email', '');

        $university = University::query()->findOrFail($tenantId);

        SubscriptionUpgradeRequest::query()->updateOrCreate(
            [
                'tenant_id' => $university->id,
                'requested_plan' => $requestedPlan,
                'status' => 'pending',
            ],
            [
                'requested_by_email' => $email,
            ],
        );

        return view('marketing.upgrade-requested');
    }
}
