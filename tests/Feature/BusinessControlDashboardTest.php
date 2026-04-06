<?php

use App\Models\Plan;
use App\Models\SuperAdmin;

test('business control dashboard requires super admin authentication', function () {
    $this->get(route('central.business-control.index'))
        ->assertRedirect(route('central.login'));
});

test('authenticated super admin can view business control dashboard', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Admin',
        'email' => 'business-control-admin@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.index'));

    $response
        ->assertOk()
        ->assertSee('Business Control')
        ->assertSee('Plan Management')
        ->assertSee('Coupon Management')
        ->assertSee('Upgrade Queue');
});

test('authenticated super admin can view all business control pages', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Pages Admin',
        'email' => 'business-control-pages-admin@example.test',
        'password' => 'password',
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.plans.index'))
        ->assertOk()
        ->assertSee('Plan Management');

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.coupons.index'))
        ->assertOk()
        ->assertSee('Coupon Management');

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.upgrade-requests.index'))
        ->assertOk()
        ->assertSee('Upgrade Requests');
});

test('plan can be created when sort order is omitted', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Plan Creator',
        'email' => 'business-control-plan-creator@example.test',
        'password' => 'password',
    ]);

    $expectedSortOrder = ((int) (Plan::query()->max('sort_order') ?? 0)) + 10;

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->post(route('central.business-control.plans.store'), [
            'code' => 'premium',
            'name' => 'Premium',
            'monthly_price' => 30,
            'yearly_price' => 360,
            'is_active' => '1',
            'feature_flags' => [
                'analytics' => '1',
                'bracket' => '1',
            ],
            'sort_order' => null,
        ]);

    $response
        ->assertRedirect(route('central.business-control.plans.index'))
        ->assertSessionHas('status', 'Plan created successfully.');

    $this->assertDatabaseHas('plans', [
        'code' => 'premium',
        'name' => 'Premium',
        'sort_order' => $expectedSortOrder,
    ]);
});

test('basic and pro plans cannot be deleted', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Plan Guard',
        'email' => 'business-control-plan-guard@example.test',
        'password' => 'password',
    ]);

    $basicPlan = Plan::query()->where('code', 'basic')->firstOrFail();

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->delete(route('central.business-control.plans.destroy', $basicPlan));

    $response
        ->assertRedirect(route('central.business-control.plans.index'))
        ->assertSessionHas('status', 'Basic and Pro plans are protected and cannot be deleted.');

    $this->assertDatabaseHas('plans', [
        'id' => $basicPlan->id,
    ]);
});

test('custom plan can be deleted', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Plan Deleter',
        'email' => 'business-control-plan-deleter@example.test',
        'password' => 'password',
    ]);

    $plan = Plan::query()->create([
        'code' => 'promo_plus',
        'name' => 'Promo Plus',
        'monthly_price' => 29,
        'yearly_price' => 299,
        'yearly_discount_percent' => 14,
        'is_active' => true,
        'sort_order' => 90,
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->delete(route('central.business-control.plans.destroy', $plan));

    $response
        ->assertRedirect(route('central.business-control.plans.index'))
        ->assertSessionHas('status', 'Plan deleted successfully.');

    $this->assertDatabaseMissing('plans', [
        'id' => $plan->id,
    ]);
});
