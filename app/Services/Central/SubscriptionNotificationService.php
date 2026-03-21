<?php

namespace App\Services\Central;

use App\Mail\TenantSubscriptionStatusMail;
use App\Models\SubscriptionNotificationLog;
use App\Models\University;
use Illuminate\Support\Facades\Mail;

class SubscriptionNotificationService
{
    public function send(University $university, string $type, array $context = [], ?string $notificationKey = null): bool
    {
        if (! is_string($university->tenant_admin_email) || $university->tenant_admin_email === '') {
            return false;
        }

        if ($notificationKey !== null && SubscriptionNotificationLog::query()->where('notification_key', $notificationKey)->exists()) {
            return false;
        }

        [$subject, $intro, $details] = $this->buildMessage($university, $type, $context);

        Mail::to($university->tenant_admin_email)->queue(new TenantSubscriptionStatusMail(
            university: $university,
            subjectLine: $subject,
            introLine: $intro,
            details: $details,
        ));

        SubscriptionNotificationLog::query()->create([
            'university_id' => $university->id,
            'recipient_email' => $university->tenant_admin_email,
            'notification_type' => $type,
            'notification_key' => $notificationKey,
            'subject' => $subject,
            'details' => $details,
            'queued_at' => now(),
        ]);

        return true;
    }

    /**
     * @return array{0:string,1:string,2:array<string,string>}
     */
    private function buildMessage(University $university, string $type, array $context): array
    {
        $startsAt = $university->subscription_starts_at?->toDateString() ?? 'Not set';
        $expiresAt = $university->expires_at?->toDateString() ?? 'No expiry';
        $domain = optional($university->domains->first())->domain ?? 'No domain';

        return match ($type) {
            'plan_started' => [
                'Your subscription has started',
                'Your tenant subscription is now active and ready to use.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'Subscription Starts' => $startsAt,
                    'Subscription Expires' => $expiresAt,
                ],
            ],
            'plan_changed' => [
                'Your subscription plan has changed',
                'Your tenant plan details were updated.',
                [
                    'School' => $university->name,
                    'Previous Plan' => strtoupper((string) ($context['previous_plan'] ?? 'UNKNOWN')),
                    'New Plan' => strtoupper($university->plan),
                    'Subscription Expires' => $expiresAt,
                ],
            ],
            'suspended' => [
                'Your subscription is suspended',
                'Your tenant subscription has been suspended. Access may be limited until reactivation.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Status' => strtoupper($university->status),
                ],
            ],
            'reactivated' => [
                'Your subscription has been reactivated',
                'Your tenant subscription is active again.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Status' => strtoupper($university->status),
                    'Subscription Expires' => $expiresAt,
                ],
            ],
            'subscription_extended' => [
                'Your subscription expiry was extended',
                'Your tenant subscription has a new expiry date.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'New Expiry Date' => $expiresAt,
                ],
            ],
            'expiring_14_days' => [
                'Subscription reminder: 14 days left',
                'Your tenant subscription will expire in 14 days.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'Expiry Date' => $expiresAt,
                ],
            ],
            'expiring_3_days' => [
                'Subscription reminder: 3 days left',
                'Your tenant subscription will expire in 3 days.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'Expiry Date' => $expiresAt,
                ],
            ],
            'expired' => [
                'Subscription expired',
                'Your tenant subscription has expired.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'Expiry Date' => $expiresAt,
                ],
            ],
            default => [
                'Subscription update',
                'Your tenant subscription details were updated.',
                [
                    'School' => $university->name,
                    'Domain' => $domain,
                    'Plan' => strtoupper($university->plan),
                    'Subscription Starts' => $startsAt,
                    'Subscription Expires' => $expiresAt,
                ],
            ],
        };
    }
}
