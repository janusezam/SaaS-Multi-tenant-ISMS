<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionNotificationLog;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionNotificationLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = SubscriptionNotificationLog::query()
            ->with('university')
            ->when($request->filled('tenant_id'), function ($query) use ($request): void {
                $query->where('university_id', (string) $request->string('tenant_id'));
            })
            ->when($request->filled('notification_type'), function ($query) use ($request): void {
                $query->where('notification_type', (string) $request->string('notification_type'));
            })
            ->when($request->filled('recipient_email'), function ($query) use ($request): void {
                $query->where('recipient_email', 'like', '%'.(string) $request->string('recipient_email').'%');
            })
            ->when($request->filled('from_date'), function ($query) use ($request): void {
                $query->whereDate('queued_at', '>=', (string) $request->string('from_date'));
            })
            ->when($request->filled('to_date'), function ($query) use ($request): void {
                $query->whereDate('queued_at', '<=', (string) $request->string('to_date'));
            })
            ->orderByDesc('queued_at')
            ->paginate(15)
            ->withQueryString();

        return view('central.subscription-notification-logs.index', [
            'logs' => $logs,
            'tenantOptions' => University::query()->orderBy('name')->get(['id', 'name']),
            'notificationTypeOptions' => SubscriptionNotificationLog::query()
                ->select('notification_type')
                ->distinct()
                ->orderBy('notification_type')
                ->pluck('notification_type'),
        ]);
    }
}
