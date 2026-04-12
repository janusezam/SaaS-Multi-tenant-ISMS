<?php

use App\Models\CampaignVersion;
use App\Models\PromotionCampaign;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\University;

test('promotion campaigns page requires super admin authentication', function () {
    $this->get(route('central.business-control.campaigns.index'))
        ->assertRedirect(route('central.login'));
});

test('super admin can create campaign and apply it to next renewals', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Campaign Admin',
        'email' => 'campaign-admin@example.test',
        'password' => 'password',
    ]);

    $basicTenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'campaign-basic-'.uniqid(),
        'name' => 'Campaign Basic University',
        'tenant_admin_name' => 'Basic Admin',
        'tenant_admin_email' => 'basic-admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addMonth(),
    ]));

    $proTenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'campaign-pro-'.uniqid(),
        'name' => 'Campaign Pro University',
        'tenant_admin_name' => 'Pro Admin',
        'tenant_admin_email' => 'pro-admin@example.test',
        'plan' => 'pro',
        'status' => 'active',
        'expires_at' => now()->addMonth(),
    ]));

    Subscription::query()->create([
        'tenant_id' => $basicTenant->id,
        'plan' => 'basic',
        'billing_cycle' => 'monthly',
        'base_price' => 19,
        'discount_amount' => 0,
        'final_price' => 19,
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addMonth()->toDateString(),
    ]);

    Subscription::query()->create([
        'tenant_id' => $proTenant->id,
        'plan' => 'pro',
        'billing_cycle' => 'yearly',
        'base_price' => 490,
        'discount_amount' => 0,
        'final_price' => 490,
        'status' => 'active',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addYear()->toDateString(),
    ]);

    $createResponse = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('central.business-control.campaigns.store'), [
            'name' => 'Black Friday 2026',
            'status' => 'active',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'target_plan_codes' => ['basic'],
            'lifecycle_policy' => 'next_renewal',
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ]);

    $createResponse->assertRedirect(route('central.business-control.campaigns.index'));

    $campaign = PromotionCampaign::query()->where('name', 'Black Friday 2026')->firstOrFail();

    $this->assertDatabaseHas('campaign_versions', [
        'promotion_campaign_id' => $campaign->id,
        'version_number' => 1,
        'name' => 'Black Friday 2026',
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);

    $applyResponse = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('central.business-control.campaigns.apply-renewals', $campaign), [
            'plan_code' => 'basic',
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

    $applyResponse->assertRedirect(route('central.business-control.campaigns.index'));

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $basicTenant->id,
        'next_renewal_campaign_id' => $campaign->id,
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $proTenant->id,
        'next_renewal_campaign_id' => null,
    ]);
});

test('campaign update appends next campaign version snapshot', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Campaign Version Admin',
        'email' => 'campaign-version-admin@example.test',
        'password' => 'password',
    ]);

    $campaign = PromotionCampaign::query()->create([
        'name' => 'Summer Saver',
        'status' => 'draft',
        'discount_type' => 'percent',
        'discount_value' => 10,
        'target_plan_codes' => ['basic'],
        'is_stackable_with_coupon' => false,
        'priority' => 100,
        'lifecycle_policy' => 'next_renewal',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDays(2),
        'description' => 'Initial campaign draft',
    ]);

    CampaignVersion::query()->create([
        'promotion_campaign_id' => $campaign->id,
        'version_number' => 1,
        'name' => $campaign->name,
        'status' => $campaign->status,
        'discount_type' => $campaign->discount_type,
        'discount_value' => $campaign->discount_value,
        'target_plan_codes' => $campaign->target_plan_codes,
        'is_stackable_with_coupon' => $campaign->is_stackable_with_coupon,
        'priority' => $campaign->priority,
        'lifecycle_policy' => $campaign->lifecycle_policy,
        'starts_at' => $campaign->starts_at,
        'ends_at' => $campaign->ends_at,
        'description' => $campaign->description,
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('central.business-control.campaigns.update', $campaign), [
            'name' => 'Summer Saver Plus',
            'status' => 'active',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'target_plan_codes' => ['pro'],
            'lifecycle_policy' => 'next_renewal',
            'starts_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'description' => 'Updated campaign details',
        ]);

    $response->assertRedirect(route('central.business-control.campaigns.index'));

    $this->assertSame(2, CampaignVersion::query()->where('promotion_campaign_id', $campaign->id)->count());

    $latestVersion = CampaignVersion::query()
        ->where('promotion_campaign_id', $campaign->id)
        ->orderByDesc('version_number')
        ->firstOrFail();

    expect($latestVersion->version_number)->toBe(2)
        ->and($latestVersion->name)->toBe('Summer Saver Plus')
        ->and($latestVersion->status)->toBe('active')
        ->and((string) $latestVersion->discount_value)->toBe('50.00')
        ->and($latestVersion->changed_by_super_admin_id)->toBe($superAdmin->id);
});

test('campaign management page shows version history', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Campaign History Viewer',
        'email' => 'campaign-history-viewer@example.test',
        'password' => 'password',
    ]);

    $campaign = PromotionCampaign::query()->create([
        'name' => 'History Campaign',
        'status' => 'active',
        'discount_type' => 'percent',
        'discount_value' => 12,
        'target_plan_codes' => ['basic'],
        'lifecycle_policy' => 'next_renewal',
    ]);

    CampaignVersion::query()->create([
        'promotion_campaign_id' => $campaign->id,
        'version_number' => 1,
        'name' => $campaign->name,
        'status' => $campaign->status,
        'discount_type' => $campaign->discount_type,
        'discount_value' => $campaign->discount_value,
        'target_plan_codes' => $campaign->target_plan_codes,
        'is_stackable_with_coupon' => false,
        'priority' => 100,
        'lifecycle_policy' => $campaign->lifecycle_policy,
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.campaigns.index'))
        ->assertOk()
        ->assertSee('Version History')
        ->assertSee('v1')
        ->assertSee('History Campaign');
});
