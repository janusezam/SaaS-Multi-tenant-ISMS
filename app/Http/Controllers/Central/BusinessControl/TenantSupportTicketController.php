<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreSystemUpdateRequest;
use App\Http\Requests\Central\UpdateTenantSupportTicketStatusRequest;
use App\Models\SystemUpdate;
use App\Models\TenantSupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSupportTicketController extends Controller
{
    public function index(): View
    {
        return view('central.business-control.support-updates.index', [
            'openTickets' => TenantSupportTicket::query()
                ->whereIn('status', ['open', 'in_progress'])
                ->latest()
                ->limit(50)
                ->get(),
            'resolvedTickets' => TenantSupportTicket::query()
                ->where('status', 'resolved')
                ->latest('resolved_at')
                ->limit(20)
                ->get(),
            'updates' => SystemUpdate::query()
                ->latest('published_at')
                ->latest('id')
                ->limit(30)
                ->get(),
        ]);
    }

    public function updateTicket(UpdateTenantSupportTicketStatusRequest $request, TenantSupportTicket $ticket): RedirectResponse
    {
        $ticket->status = $request->validated('status');
        $ticket->central_note = $request->validated('central_note');
        $ticket->resolved_at = $ticket->status === 'resolved' ? now() : null;
        $ticket->save();

        return redirect()
            ->route('central.business-control.support-updates.index')
            ->with('status', 'Support ticket updated.');
    }

    public function storeUpdate(StoreSystemUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        SystemUpdate::query()->create([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'version' => $validated['version'] ?? null,
            'source' => $validated['source'],
            'is_published' => (bool) ($validated['is_published'] ?? true),
            'published_at' => $validated['published_at'] ?? now(),
            'created_by_super_admin_id' => auth('super_admin')->id(),
        ]);

        return redirect()
            ->route('central.business-control.support-updates.index')
            ->with('status', 'System update published.');
    }
}
