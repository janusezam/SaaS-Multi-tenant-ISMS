<?php

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
