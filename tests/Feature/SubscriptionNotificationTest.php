<?php

use App\Mail\TenantSubscriptionStatusMail;
use App\Models\SubscriptionNotificationLog;
use App\Models\SuperAdmin;
use App\Models\University;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

test('creating university queues plan started subscription email and logs notification', function () {
    Event::fake();
    Mail::fake();

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'subscription-create-admin@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->post(route('central.universities.store'), [
        'name' => 'Notify University',
        'school_address' => 'Notification Street',
        'tenant_admin_name' => 'Notify Admin',
        'tenant_admin_email' => 'notify.admin@example.test',
        'subdomain' => 'notify-uni',
        'plan' => 'pro',
        'subscription_starts_at' => now()->toDateString(),
        'expires_at' => now()->addDays(30)->toDateString(),
    ]);

    $response->assertRedirect(route('central.universities.index'));

    Mail::assertQueued(TenantSubscriptionStatusMail::class, function (TenantSubscriptionStatusMail $mail): bool {
        return $mail->hasTo('notify.admin@example.test')
            && $mail->subjectLine === 'Your subscription has started';
    });

    $this->assertDatabaseHas('subscription_notification_logs', [
        'university_id' => 'notify-uni',
        'recipient_email' => 'notify.admin@example.test',
        'notification_type' => 'plan_started',
    ]);
});

test('updating university status queues suspended email and logs notification', function () {
    Mail::fake();

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'subscription-update-admin@example.test',
        'password' => 'password',
    ]);

    $university = University::withoutEvents(fn () => University::query()->create([
        'id' => 'notify-status',
        'name' => 'Status University',
        'school_address' => 'Status Street',
        'tenant_admin_name' => 'Status Admin',
        'tenant_admin_email' => 'status.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->addDays(10),
    ]));

    $university->domains()->create([
        'domain' => 'notify-status.isms.test',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->put(route('central.universities.update', $university), [
        'name' => 'Status University',
        'school_address' => 'Status Street',
        'tenant_admin_name' => 'Status Admin',
        'tenant_admin_email' => 'status.admin@example.test',
        'plan' => 'basic',
        'status' => 'suspended',
        'subscription_starts_at' => now()->toDateString(),
        'expires_at' => now()->addDays(10)->toDateString(),
    ]);

    $response->assertRedirect(route('central.universities.index'));

    Mail::assertQueued(TenantSubscriptionStatusMail::class, function (TenantSubscriptionStatusMail $mail): bool {
        return $mail->hasTo('status.admin@example.test')
            && $mail->subjectLine === 'Your subscription is suspended';
    });

    $this->assertDatabaseHas('subscription_notification_logs', [
        'university_id' => 'notify-status',
        'notification_type' => 'suspended',
    ]);
});

test('subscription reminder command queues expiring and expired notifications once per key', function () {
    Mail::fake();

    $expiringUniversity = University::withoutEvents(fn () => University::query()->create([
        'id' => 'reminder-14',
        'name' => 'Reminder University',
        'school_address' => 'Reminder Street',
        'tenant_admin_name' => 'Reminder Admin',
        'tenant_admin_email' => 'reminder.admin@example.test',
        'plan' => 'pro',
        'status' => 'active',
        'subscription_starts_at' => now(),
        'expires_at' => now()->startOfDay()->addDays(14),
    ]));

    $expiredUniversity = University::withoutEvents(fn () => University::query()->create([
        'id' => 'expired-uni',
        'name' => 'Expired University',
        'school_address' => 'Expired Street',
        'tenant_admin_name' => 'Expired Admin',
        'tenant_admin_email' => 'expired.admin@example.test',
        'plan' => 'basic',
        'status' => 'active',
        'subscription_starts_at' => now()->subDays(60),
        'expires_at' => now()->subDay(),
    ]));

    $expiringUniversity->domains()->create(['domain' => 'reminder-14.isms.test']);
    $expiredUniversity->domains()->create(['domain' => 'expired-uni.isms.test']);

    $this->artisan('subscriptions:send-reminders')->assertSuccessful();
    $this->artisan('subscriptions:send-reminders')->assertSuccessful();

    $this->assertDatabaseHas('subscription_notification_logs', [
        'university_id' => 'reminder-14',
        'notification_type' => 'expiring_14_days',
    ]);

    $this->assertDatabaseHas('subscription_notification_logs', [
        'university_id' => 'expired-uni',
        'notification_type' => 'expired',
    ]);

    expect(SubscriptionNotificationLog::query()->where('university_id', 'reminder-14')->count())->toBe(1);
    expect(SubscriptionNotificationLog::query()->where('university_id', 'expired-uni')->count())->toBe(1);

    Mail::assertQueued(TenantSubscriptionStatusMail::class);
});
