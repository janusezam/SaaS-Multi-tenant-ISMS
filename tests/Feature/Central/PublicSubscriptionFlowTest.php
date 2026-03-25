<?php

use App\Models\University;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

test('public user can view landing and pricing pages', function () {
    $this->get(route('public.landing'))->assertOk();
    $this->get(route('public.pricing'))->assertOk();
});

test('public pricing signup creates pending tenant and subscription', function () {
    $tenantId = 'public-signup-'.Str::lower(Str::random(6));

    $response = $this->post(route('public.subscribe'), [
        'name' => 'Public Signup University',
        'school_address' => 'Public District',
        'tenant_admin_name' => 'Public Admin',
        'tenant_admin_email' => 'public.admin@example.test',
        'subdomain' => $tenantId,
        'plan' => 'basic',
    ]);

    $response->assertRedirect(route('public.pricing'));

    $this->assertDatabaseHas('tenants', [
        'id' => $tenantId,
        'status' => 'pending',
        'plan' => 'basic',
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $tenantId,
        'status' => 'pending',
        'plan' => 'basic',
    ]);

    $this->assertDatabaseHas('domains', [
        'tenant_id' => $tenantId,
    ]);
});

test('signed upgrade request from tenant context is stored centrally', function () {
    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'upgrade-tenant',
        'name' => 'Upgrade Tenant University',
        'plan' => 'basic',
        'status' => 'active',
    ]));

    $signedUrl = URL::temporarySignedRoute('central.upgrade.requests.store', now()->addMinutes(5), [
        'tenant' => $university->id,
        'plan' => 'pro',
        'email' => 'admin@upgrade-tenant.test',
    ]);

    $response = $this->get($signedUrl);

    $response->assertOk();

    $this->assertDatabaseHas('subscription_upgrade_requests', [
        'tenant_id' => 'upgrade-tenant',
        'requested_plan' => 'pro',
        'status' => 'pending',
        'requested_by_email' => 'admin@upgrade-tenant.test',
    ]);
});
