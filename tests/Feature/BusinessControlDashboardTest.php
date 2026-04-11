<?php

use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\TenantSupportTicket;

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
        ->assertSee('Campaign Management')
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
        ->get(route('central.business-control.upgrade-requests.index'))
        ->assertOk()
        ->assertSee('Upgrade Requests');

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.campaigns.index'))
        ->assertOk()
        ->assertSee('Campaign Management');

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.support-updates.index'))
        ->assertOk()
        ->assertSee('Support & Updates');
});

test('super admin can post updates and resolve tenant reports', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Support Queue Admin',
        'email' => 'support-queue-admin@example.test',
        'password' => 'password',
    ]);

    $ticket = TenantSupportTicket::query()->create([
        'tenant_id' => 'tenant-alpha',
        'tenant_name' => 'Tenant Alpha',
        'reported_by_user_id' => 1,
        'reported_by_name' => 'Tenant User',
        'reported_by_email' => 'tenant-user@example.test',
        'reported_by_role' => 'university_admin',
        'category' => 'bug',
        'subject' => 'Scoreboard refresh issue',
        'message' => 'Scoreboard does not refresh after result update.',
        'status' => 'open',
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->post(route('central.business-control.support-updates.updates.store'), [
            'title' => 'April Maintenance Rollup',
            'summary' => 'Improved support tooling and tenant profile controls.',
            'version' => 'v1.1.0',
            'source' => 'manual',
            'is_published' => 1,
        ])
        ->assertRedirect(route('central.business-control.support-updates.index'));

    $this->assertDatabaseHas('system_updates', [
        'title' => 'April Maintenance Rollup',
        'version' => 'v1.1.0',
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('central.business-control.support-updates.tickets.update', $ticket), [
            'status' => 'resolved',
            'central_note' => 'Patched and deployed.',
        ])
        ->assertRedirect(route('central.business-control.support-updates.index'));

    $this->assertDatabaseHas('tenant_support_tickets', [
        'id' => $ticket->id,
        'status' => 'resolved',
        'central_note' => 'Patched and deployed.',
    ]);
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
