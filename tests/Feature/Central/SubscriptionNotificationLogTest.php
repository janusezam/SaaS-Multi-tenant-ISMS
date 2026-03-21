<?php

use App\Models\SubscriptionNotificationLog;
use App\Models\SuperAdmin;
use App\Models\University;
use App\Models\User;

test('authenticated super admin can view subscription notification logs', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'logs-admin@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'logs-uni',
        'name' => 'Logs University',
        'school_address' => 'Logs Street',
        'tenant_admin_name' => 'Logs Admin',
        'tenant_admin_email' => 'logs.tenant@example.test',
        'plan' => 'pro',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(30),
    ]));

    SubscriptionNotificationLog::query()->create([
        'university_id' => $university->id,
        'recipient_email' => 'logs.tenant@example.test',
        'notification_type' => 'plan_started',
        'subject' => 'Your subscription has started',
        'details' => ['Plan' => 'PRO'],
        'queued_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.subscription-notification-logs.index'));

    $response->assertOk();
    $response->assertSee('Subscription Notification Logs');
    $response->assertSee('logs.tenant@example.test');
});

test('super admin can filter subscription notification logs', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'logs-filter-admin@example.test',
        'password' => 'password',
    ]);

    $targetUniversity = University::withoutEvents(fn () => University::query()->create([
        'id' => 'target-uni',
        'name' => 'Target University',
        'school_address' => 'Target Street',
        'tenant_admin_name' => 'Target Admin',
        'tenant_admin_email' => 'target@example.test',
        'plan' => 'pro',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(30),
    ]));

    $otherUniversity = University::withoutEvents(fn () => University::query()->create([
        'id' => 'other-uni',
        'name' => 'Other University',
        'school_address' => 'Other Street',
        'tenant_admin_name' => 'Other Admin',
        'tenant_admin_email' => 'other@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(5),
    ]));

    SubscriptionNotificationLog::query()->create([
        'university_id' => $targetUniversity->id,
        'recipient_email' => 'target@example.test',
        'notification_type' => 'expiring_14_days',
        'subject' => 'Subscription reminder: 14 days left',
        'details' => ['Plan' => 'PRO'],
        'queued_at' => now(),
    ]);

    SubscriptionNotificationLog::query()->create([
        'university_id' => $otherUniversity->id,
        'recipient_email' => 'other@example.test',
        'notification_type' => 'expired',
        'subject' => 'Subscription expired',
        'details' => ['Plan' => 'BASIC'],
        'queued_at' => now(),
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.subscription-notification-logs.index', [
        'tenant_id' => 'target-uni',
        'notification_type' => 'expiring_14_days',
    ]));

    $response->assertOk();
    $response->assertSee('target@example.test');
    $response->assertDontSee('other@example.test');
});

test('non super admin cannot access subscription notification logs', function () {
    $user = User::factory()->create([
        'role' => 'student_player',
    ]);

    $response = $this->actingAs($user)->get(route('central.subscription-notification-logs.index'));

    $response->assertRedirect(route('central.login'));
});
