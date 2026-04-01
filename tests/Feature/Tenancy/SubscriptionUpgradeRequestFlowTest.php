<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionUpgradeRequest;
use App\Models\University;
use App\Models\User;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').'upgrade-flow-tenant'.(string) config('tenancy.database.suffix', ''));

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializeUpgradeTenant(University $tenant): void
{
    $databasePath = database_path((string) config('tenancy.database.prefix', 'tenant_').$tenant->id.(string) config('tenancy.database.suffix', ''));

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    tenancy()->initialize($tenant);
}

test('tenant admin can preview pricing and submit upgrade request', function () {
    Plan::query()->updateOrCreate([
        'code' => 'basic',
    ], [
        'name' => 'Basic',
        'monthly_price' => 19,
        'yearly_price' => 190,
        'yearly_discount_percent' => 16.67,
        'is_active' => true,
        'sort_order' => 10,
    ]);

    Plan::query()->updateOrCreate([
        'code' => 'pro',
    ], [
        'name' => 'Pro',
        'monthly_price' => 49,
        'yearly_price' => 490,
        'yearly_discount_percent' => 16.67,
        'is_active' => true,
        'sort_order' => 20,
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'upgrade-flow-tenant',
        'name' => 'Upgrade Flow University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]));

    Subscription::query()->updateOrCreate([
        'tenant_id' => $tenant->id,
    ], [
        'plan' => 'basic',
        'billing_cycle' => 'monthly',
        'base_price' => 19,
        'discount_amount' => 0,
        'final_price' => 19,
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
    ]);

    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    initializeUpgradeTenant($tenant);

    $previewResponse = $this->actingAs($user)->getJson(route('tenant.subscription.preview', [
        'plan' => 'pro',
        'billing_cycle' => 'monthly',
    ]));

    $previewResponse->assertOk();
    $previewResponse->assertJsonPath('quote.plan.code', 'pro');

    $submitResponse = $this->actingAs($user)->postJson(route('tenant.subscription.upgrade-requests.store'), [
        'requested_plan' => 'pro',
        'billing_cycle' => 'yearly',
    ]);

    $submitResponse->assertOk();
    $submitResponse->assertJsonPath('status', 'pending');

    tenancy()->end();

    $this->assertDatabaseHas('subscription_upgrade_requests', [
        'tenant_id' => $tenant->id,
        'requested_plan' => 'pro',
        'billing_cycle' => 'yearly',
        'status' => 'pending',
    ]);
});

test('duplicate pending upgrade request is blocked', function () {
    Plan::query()->updateOrCreate([
        'code' => 'pro',
    ], [
        'name' => 'Pro',
        'monthly_price' => 49,
        'yearly_price' => 490,
        'yearly_discount_percent' => 16.67,
        'is_active' => true,
        'sort_order' => 20,
    ]);

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'upgrade-flow-tenant',
        'name' => 'Upgrade Flow University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]));

    $user = User::factory()->create([
        'role' => 'university_admin',
    ]);

    initializeUpgradeTenant($tenant);

    SubscriptionUpgradeRequest::query()->create([
        'tenant_id' => $tenant->id,
        'requested_plan' => 'pro',
        'billing_cycle' => 'monthly',
        'requested_by_email' => $user->email,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->postJson(route('tenant.subscription.upgrade-requests.store'), [
        'requested_plan' => 'pro',
        'billing_cycle' => 'monthly',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['request']);
});
