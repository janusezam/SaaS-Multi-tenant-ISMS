<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ApproveUniversityRequest;
use App\Http\Requests\Central\ExtendUniversitySubscriptionRequest;
use App\Http\Requests\Central\StoreUniversityRequest;
use App\Http\Requests\Central\UpdateUniversityRequest;
use App\Mail\TenantAdminInviteMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\University;
use App\Models\User;
use App\Services\Central\SubscriptionNotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Database\SQLiteDatabaseDoesNotExistException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stancl\Tenancy\Database\DatabaseManager as TenancyDatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Stancl\Tenancy\Jobs\SeedDatabase;

class UniversityController extends Controller
{
    public function __construct(private SubscriptionNotificationService $subscriptionNotificationService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $universities = University::query()
            ->with(['domains', 'subscription'])
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
        return view('central.universities.create', [
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUniversityRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $subscriptionStartDate = $validated['subscription_starts_at'] ?? now()->toDateString();

        $university = University::query()->create([
            'id' => $validated['subdomain'],
            'name' => $validated['name'],
            'school_address' => $validated['school_address'],
            'tenant_admin_name' => $validated['tenant_admin_name'],
            'tenant_admin_email' => $validated['tenant_admin_email'],
            'plan' => $validated['plan'],
            'status' => 'active',
            'subscription_starts_at' => $subscriptionStartDate,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $university->domains()->create([
            'domain' => $validated['tenant_domain'],
        ]);

        Subscription::query()->create([
            'tenant_id' => $university->id,
            'plan' => $validated['plan'],
            'start_date' => $subscriptionStartDate,
            'due_date' => $validated['expires_at'] ?? null,
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $tenantDomain = (string) ($university->domains()->value('domain') ?? '');

        $this->syncTenantAdminUser(
            $university,
            (string) $validated['tenant_admin_name'],
            (string) $validated['tenant_admin_email'],
            $tenantDomain,
            true,
        );

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'School created and activated. Tenant admin invite sent.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(University $university): View
    {
        return view('central.universities.edit', [
            'university' => $university,
            'plans' => Plan::query()->active()->orderBy('sort_order')->get(),
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

        $subscription = $university->subscription;

        DB::transaction(function () use ($validated, $university, $subscription): void {
            $university->update($validated);

            if ($subscription !== null) {
                $subscription->update([
                    'plan' => $validated['plan'],
                    'status' => $validated['status'],
                    'start_date' => $validated['subscription_starts_at'] ?? null,
                    'due_date' => $validated['expires_at'] ?? null,
                    'approved_at' => $validated['status'] === 'active' ? now() : $subscription->approved_at,
                ]);
            }
        });

        $tenantDomain = (string) ($university->domains()->value('domain') ?? '');

        $forceInvite = $originalStatus === 'pending' && $university->status === 'active';
        $adminEmailChanged = $originalAdminEmail !== $validated['tenant_admin_email'];

        if ($forceInvite || $adminEmailChanged) {
            $this->syncTenantAdminUser(
                $university,
                $validated['tenant_admin_name'],
                $validated['tenant_admin_email'],
                $tenantDomain,
                $forceInvite || $adminEmailChanged,
            );
        }

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

            if ($university->status === 'expired') {
                $this->subscriptionNotificationService->send($university, 'expired');
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
     * Approve and activate a pending university.
     */
    public function approve(ApproveUniversityRequest $request, University $university): RedirectResponse
    {
        $subscription = $university->subscription;

        if ($subscription === null) {
            return redirect()
                ->route('central.universities.index')
                ->with('status', 'Approval failed. Subscription record is missing.');
        }

        try {
            $this->provisionTenantInfrastructureIfMissing($university);
        } catch (\Throwable) {
            return redirect()
                ->route('central.universities.index')
                ->with('status', 'Approval failed while provisioning tenant database. Please retry approval.');
        }

        $manualPriceOverride = $request->validated('manual_price_override');

        DB::transaction(function () use ($university, $subscription, $manualPriceOverride): void {
            $finalPrice = $manualPriceOverride !== null
                ? (float) $manualPriceOverride
                : $subscription->final_price;

            $snapshot = is_array($subscription->pricing_snapshot)
                ? $subscription->pricing_snapshot
                : [];

            if ($manualPriceOverride !== null) {
                $snapshot['final_price'] = $finalPrice;
            }

            $subscription->update([
                'status' => 'active',
                'final_price' => $finalPrice,
                'pricing_snapshot' => $snapshot === [] ? null : $snapshot,
                'start_date' => $subscription->start_date ?? now()->toDateString(),
                'approved_at' => now(),
            ]);

            $university->update([
                'plan' => $subscription->plan,
                'status' => 'active',
                'subscription_starts_at' => $subscription->start_date,
                'expires_at' => $subscription->due_date,
            ]);
        });

        $tenantDomain = (string) ($university->domains()->value('domain') ?? '');

        $this->syncTenantAdminUser(
            $university,
            (string) $university->tenant_admin_name,
            (string) $university->tenant_admin_email,
            $tenantDomain,
            true,
        );

        $university->load('domains');

        $this->subscriptionNotificationService->send($university, 'plan_started');

        return redirect()
            ->route('central.universities.index')
            ->with('status', 'School approved and activated. Tenant admin invite sent.');
    }

    private function provisionTenantInfrastructureIfMissing(University $university): void
    {
        $databaseName = $university->database()->getName();
        $databaseManager = $university->database()->manager();

        if ($databaseManager->databaseExists($databaseName)) {
            return;
        }

        // Public signup stores this flag as false; approval explicitly enables provisioning.
        $university->setInternal('create_database', true);
        $university->save();

        (new CreateDatabase($university))->handle(app(TenancyDatabaseManager::class));
        (new MigrateDatabase($university))->handle();
        (new SeedDatabase($university))->handle();
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
        DB::transaction(function () use ($university): void {
            $university->update([
                'status' => 'suspended',
            ]);

            $university->subscription()?->update([
                'status' => 'expired',
            ]);
        });

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
        DB::transaction(function () use ($university): void {
            $university->update([
                'status' => 'active',
            ]);

            $university->subscription()?->update([
                'status' => 'active',
                'approved_at' => now(),
            ]);
        });

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

        $newExpiry = $baseDate->copy()->addDays($request->integer('days'));

        DB::transaction(function () use ($university, $newExpiry): void {
            $university->update([
                'expires_at' => $newExpiry,
            ]);

            $university->subscription()?->update([
                'due_date' => $newExpiry->toDateString(),
            ]);
        });

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
