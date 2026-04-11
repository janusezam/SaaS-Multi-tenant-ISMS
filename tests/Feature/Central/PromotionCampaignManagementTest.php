<?php

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
