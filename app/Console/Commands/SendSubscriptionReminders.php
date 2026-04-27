<?php

namespace App\Console\Commands;

use App\Models\University;
use App\Services\Central\SubscriptionNotificationService;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send subscription reminder and expiry notifications to tenant admins';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionNotificationService $notificationService): int
    {
        $today = now()->startOfDay();
        $sent = 0;

        $universities = University::query()
            ->with('domains')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereNotNull('tenant_admin_email')
            ->get();

        foreach ($universities as $university) {
            $expiresAt = $university->expires_at?->startOfDay();

            if ($expiresAt === null) {
                continue;
            }

            $daysUntilExpiry = $today->diffInDays($expiresAt, false);

            if ($daysUntilExpiry <= 14 && $daysUntilExpiry > 3) {
                $key = sprintf('%s:expiring_14_days:%s', $university->id, $expiresAt->toDateString());
                $sent += $notificationService->send($university, 'expiring_14_days', [], $key) ? 1 : 0;

                continue;
            }

            if ($daysUntilExpiry <= 3 && $daysUntilExpiry > 0) {
                $key = sprintf('%s:expiring_3_days:%s', $university->id, $expiresAt->toDateString());
                $sent += $notificationService->send($university, 'expiring_3_days', [], $key) ? 1 : 0;

                continue;
            }

            if ($daysUntilExpiry <= 0) {
                $key = sprintf('%s:expired:%s', $university->id, $expiresAt->toDateString());
                $sent += $notificationService->send($university, 'expired', [], $key) ? 1 : 0;
            }
        }

        $this->info(sprintf('Subscription reminder run complete. Notifications queued: %d', $sent));

        return self::SUCCESS;
    }
}
