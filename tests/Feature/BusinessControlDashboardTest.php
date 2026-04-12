<?php

use App\Models\Plan;
use App\Models\PlanVersion;
use App\Models\SuperAdmin;
use App\Models\SystemUpdate;
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

test('super admin can sync current app version as a system update', function () {
    config()->set('app.version', 'v2.0.1');

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Version Sync Admin',
        'email' => 'version-sync-admin@example.test',
        'password' => 'password',
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->post(route('central.business-control.support-updates.sync-current-version'))
        ->assertRedirect(route('central.business-control.support-updates.index'))
        ->assertSessionHas('status', function (string $status): bool {
            return str_contains($status, 'v2.0.1')
                && (str_contains($status, 'Published system update') || str_contains($status, 'already exists'));
        });

    expect(SystemUpdate::query()->where('version', 'v2.0.1')->count())->toBe(1);
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

    $plan = Plan::query()->where('code', 'premium')->firstOrFail();

    $this->assertDatabaseHas('plan_versions', [
        'plan_id' => $plan->id,
        'version_number' => 1,
        'code' => 'premium',
        'name' => 'Premium',
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);
});

test('plan update appends next plan version snapshot', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Business Control Plan Versioner',
        'email' => 'business-control-plan-versioner@example.test',
        'password' => 'password',
    ]);

    $plan = Plan::query()->create([
        'code' => 'starter',
        'name' => 'Starter',
        'monthly_price' => 20,
        'yearly_price' => 200,
        'yearly_discount_percent' => 16.67,
        'max_users' => 15,
        'max_teams' => 8,
        'max_sports' => 4,
        'feature_flags' => [
            'analytics' => false,
            'bracket' => false,
        ],
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 30,
    ]);

    PlanVersion::query()->create([
        'plan_id' => $plan->id,
        'version_number' => 1,
        'code' => $plan->code,
        'name' => $plan->name,
        'monthly_price' => $plan->monthly_price,
        'yearly_price' => $plan->yearly_price,
        'yearly_discount_percent' => $plan->yearly_discount_percent,
        'max_users' => $plan->max_users,
        'max_teams' => $plan->max_teams,
        'max_sports' => $plan->max_sports,
        'feature_flags' => $plan->feature_flags,
        'is_active' => $plan->is_active,
        'is_featured' => $plan->is_featured,
        'sort_order' => $plan->sort_order,
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')
        ->patch(route('central.business-control.plans.update', $plan), [
            'code' => 'starter',
            'name' => 'Starter Plus',
            'marketing_tagline' => 'Best for growing leagues',
            'badge_label' => 'Popular',
            'cta_label' => 'Upgrade Now',
            'marketing_points' => "Faster setup\nBetter insights",
            'monthly_price' => 24,
            'yearly_price' => 240,
            'max_users' => 25,
            'max_teams' => 10,
            'max_sports' => 6,
            'feature_flags' => [
                'analytics' => '1',
                'bracket' => '0',
            ],
            'is_active' => '1',
            'is_featured' => '0',
            'sort_order' => 30,
        ]);

    $response
        ->assertRedirect(route('central.business-control.plans.index'))
        ->assertSessionHas('status', 'Plan updated successfully.');

    $this->assertSame(2, PlanVersion::query()->where('plan_id', $plan->id)->count());

    $latestVersion = PlanVersion::query()
        ->where('plan_id', $plan->id)
        ->orderByDesc('version_number')
        ->firstOrFail();

    expect($latestVersion->version_number)->toBe(2)
        ->and($latestVersion->name)->toBe('Starter Plus')
        ->and((string) $latestVersion->monthly_price)->toBe('24.00')
        ->and($latestVersion->changed_by_super_admin_id)->toBe($superAdmin->id);
});

test('plan management page shows version history', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Plan History Viewer',
        'email' => 'plan-history-viewer@example.test',
        'password' => 'password',
    ]);

    $plan = Plan::query()->create([
        'code' => 'history_plus',
        'name' => 'History Plus',
        'monthly_price' => 33,
        'yearly_price' => 330,
        'yearly_discount_percent' => 16.67,
        'is_active' => true,
        'sort_order' => 120,
    ]);

    PlanVersion::query()->create([
        'plan_id' => $plan->id,
        'version_number' => 1,
        'code' => $plan->code,
        'name' => $plan->name,
        'monthly_price' => $plan->monthly_price,
        'yearly_price' => $plan->yearly_price,
        'yearly_discount_percent' => $plan->yearly_discount_percent,
        'is_active' => $plan->is_active,
        'sort_order' => $plan->sort_order,
        'changed_by_super_admin_id' => $superAdmin->id,
    ]);

    $this->actingAs($superAdmin, 'super_admin')
        ->get(route('central.business-control.plans.index'))
        ->assertOk()
        ->assertSee('Version History')
        ->assertSee('v1')
        ->assertSee('History Plus');
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
