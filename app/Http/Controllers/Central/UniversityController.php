<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ExtendUniversitySubscriptionRequest;
use App\Http\Requests\Central\StoreUniversityRequest;
use App\Http\Requests\Central\UpdateUniversityRequest;
use App\Mail\TenantAdminInviteMail;
use App\Models\University;
use App\Models\User;
use App\Services\Central\SubscriptionNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteDatabaseDoesNotExistException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

        $this->syncTenantAdminUser(
            $university,
            $validated['tenant_admin_name'],
            $validated['tenant_admin_email'],
            $validated['tenant_domain'],
            true,
        );

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'plan_started');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'School created successfully.');
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
        $originalAdminEmail = $university->tenant_admin_email;

        $validated = $request->validated();

        $university->update($validated);

        $tenantDomain = (string) ($university->domains()->value('domain') ?? '');

        $this->syncTenantAdminUser(
            $university,
            $validated['tenant_admin_name'],
            $validated['tenant_admin_email'],
            $tenantDomain,
            $originalAdminEmail !== $validated['tenant_admin_email'],
        );

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
            ->with('status', 'School updated successfully.');
    }

    /**
     * Remove the specified university from storage.
     */
    public function destroy(University $university): RedirectResponse
    {
        try {
            $university->delete();
        } catch (QueryException $exception) {
            if (! $this->isMissingTenantDatabaseDropError($exception)) {
                throw $exception;
            }

            // The tenant row may already be deleted before the DB drop is attempted.
            University::query()->whereKey($university->getKey())->delete();
        }

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'School deleted successfully.');
    }

    private function isMissingTenantDatabaseDropError(QueryException $exception): bool
    {
        $errorMessage = strtolower($exception->getMessage());

        return str_contains($errorMessage, "can't drop database")
            && str_contains($errorMessage, "doesn't exist");
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
            ->with('status', 'School suspended successfully.');
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
            ->with('status', 'School reactivated successfully.');
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
            ->with('status', 'School subscription extended successfully.');
    }

    private function syncTenantAdminUser(
        University $university,
        string $name,
        string $email,
        string $tenantDomain,
        bool $forceInvite = false,
    ): void {
        $inviteToken = null;

        tenancy()->initialize($university);

        try {
            if (! Schema::hasTable('users')) {
                return;
            }

            $hasMustChangePasswordColumn = Schema::hasColumn('users', 'must_change_password');
            $hasInviteTokenHashColumn = Schema::hasColumn('users', 'invite_token_hash');
            $hasInviteExpiresAtColumn = Schema::hasColumn('users', 'invite_expires_at');
            $hasInviteSentAtColumn = Schema::hasColumn('users', 'invite_sent_at');
            $supportsInviteToken = $hasInviteTokenHashColumn && $hasInviteExpiresAtColumn;

            $tenantAdmin = User::query()->where('role', 'university_admin')->first();

            $shouldSendInvite = false;

            if ($tenantAdmin === null) {
                $attributes = [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'university_admin',
                    'password' => Str::random(40),
                ];

                if ($hasMustChangePasswordColumn) {
                    $attributes['must_change_password'] = true;
                }

                $tenantAdmin = User::query()->create($attributes);
                $shouldSendInvite = true;
            } else {
                $tenantAdmin->update([
                    'name' => $name,
                    'email' => $email,
                ]);
            }

            if ($supportsInviteToken && ($shouldSendInvite || $forceInvite)) {
                $inviteToken = Str::random(64);

                $inviteAttributes = [
                    'invite_token_hash' => hash('sha256', $inviteToken),
                    'invite_expires_at' => now()->addDays(2),
                ];

                if ($hasInviteSentAtColumn) {
                    $inviteAttributes['invite_sent_at'] = now();
                }

                if ($hasMustChangePasswordColumn) {
                    $inviteAttributes['must_change_password'] = true;
                }

                $tenantAdmin->update($inviteAttributes);
            }
        } catch (QueryException|SQLiteDatabaseDoesNotExistException) {
            return;
        } finally {
            tenancy()->end();
        }

        if ($inviteToken !== null && $tenantDomain !== '') {
            Mail::to($email)->queue(new TenantAdminInviteMail(
                university: $university,
                recipientName: $name,
                inviteUrl: $this->buildTenantAdminInviteUrl($tenantDomain, $inviteToken, $email),
            ));
        }
    }

    private function buildTenantAdminInviteUrl(string $tenantDomain, string $token, string $email): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http';

        return sprintf(
            '%s://%s/app/admin-invite/%s?email=%s',
            $scheme,
            $tenantDomain,
            rawurlencode($token),
            rawurlencode($email),
        );
    }
}
