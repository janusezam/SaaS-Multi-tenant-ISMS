<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ExtendUniversitySubscriptionRequest;
use App\Http\Requests\Central\StoreUniversityRequest;
use App\Http\Requests\Central\UpdateUniversityRequest;
use App\Models\University;
use App\Services\Central\SubscriptionNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UniversityController extends Controller
{
    public function __construct(private SubscriptionNotificationService $subscriptionNotificationService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $universities = University::query()
            ->with('domains')
            ->latest()
            ->paginate(12);

        return view('central.universities.index', [
            'universities' => $universities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('central.universities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $university = University::query()->create([
            'id' => $validated['subdomain'],
            'name' => $validated['name'],
            'school_address' => $validated['school_address'],
            'tenant_admin_name' => $validated['tenant_admin_name'],
            'tenant_admin_email' => $validated['tenant_admin_email'],
            'plan' => $validated['plan'],
            'status' => 'active',
            'subscription_starts_at' => $validated['subscription_starts_at'] ?? now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $university->domains()->create([
            'domain' => $validated['tenant_domain'],
        ]);

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'plan_started');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(University $university): View
    {
        return view('central.universities.edit', [
            'university' => $university,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $originalPlan = $university->plan;
        $originalStatus = $university->status;

        $university->update($request->validated());
        $university->load('domains');

        if ($originalPlan !== $university->plan) {
            $this->subscriptionNotificationService->send($university, 'plan_changed', [
                'previous_plan' => $originalPlan,
            ]);
        }

        if ($originalStatus !== $university->status) {
            if ($university->status === 'suspended') {
                $this->subscriptionNotificationService->send($university, 'suspended');
            }

            if ($university->status === 'active') {
                $this->subscriptionNotificationService->send($university, 'reactivated');
            }
        }

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University updated successfully.');
    }

    /**
     * Remove the specified university from storage.
     */
    public function destroy(University $university): RedirectResponse
    {
        $university->delete();

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University deleted successfully.');
    }

    /**
     * Suspend the specified university.
     */
    public function suspend(University $university): RedirectResponse
    {
        $university->update([
            'status' => 'suspended',
        ]);

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'suspended');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University suspended successfully.');
    }

    /**
     * Reactivate the specified university.
     */
    public function reactivate(University $university): RedirectResponse
    {
        $university->update([
            'status' => 'active',
        ]);

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'reactivated');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University reactivated successfully.');
    }

    /**
     * Extend subscription of the specified university.
     */
    public function extend(ExtendUniversitySubscriptionRequest $request, University $university): RedirectResponse
    {
        $baseDate = $university->expires_at !== null && $university->expires_at->isFuture()
            ? $university->expires_at
            : now();

        $university->update([
            'expires_at' => $baseDate->copy()->addDays($request->integer('days')),
        ]);

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'subscription_extended');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'University subscription extended successfully.');
    }
}
