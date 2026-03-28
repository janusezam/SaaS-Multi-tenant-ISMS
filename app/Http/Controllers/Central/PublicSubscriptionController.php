<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StorePublicSubscriptionRequest;
use App\Models\Subscription;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicSubscriptionController extends Controller
{
    public function landing(): View
    {
        return view('marketing.landing');
    }

    public function pricing(): View
    {
        return view('marketing.pricing');
    }

    public function subscribe(StorePublicSubscriptionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $university = new University([
            'id' => $validated['subdomain'],
            'name' => $validated['name'],
            'school_address' => $validated['school_address'],
            'tenant_admin_name' => $validated['tenant_admin_name'],
            'tenant_admin_email' => $validated['tenant_admin_email'],
            'plan' => $validated['plan'],
            'status' => 'pending',
            'subscription_starts_at' => null,
            'expires_at' => null,
        ]);

        // Public signup should not provision tenant infrastructure until central approval.
        $university->setInternal('create_database', false);
        $university->save();

        $university->domains()->create([
            'domain' => $validated['tenant_domain'],
        ]);

        Subscription::query()->create([
            'tenant_id' => $university->id,
            'plan' => $validated['plan'],
            'status' => 'pending',
            'start_date' => null,
            'due_date' => null,
        ]);

        return redirect()
            ->route('public.pricing')
            ->with('status', 'Subscription request submitted. A central administrator will review and activate your tenant.');
    }
}
