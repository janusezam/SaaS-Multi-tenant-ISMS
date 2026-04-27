<?php

namespace App\Http\Controllers\Central\BusinessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreSystemUpdateRequest;
use App\Http\Requests\Central\UpdateTenantSupportTicketStatusRequest;
use App\Models\SystemUpdate;
use App\Models\TenantSupportTicket;
use App\Services\GitHubLatestReleaseService;
use App\Services\GitHubReleasePublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSupportTicketController extends Controller
{
    public function index(): View
    {
        $latestRelease = app(GitHubLatestReleaseService::class)->latest();
        $latestTag = (string) ($latestRelease['tag'] ?? '');
        $suggestedUpdateVersion = app(GitHubReleasePublisher::class)->suggestNextTag($latestTag !== '' ? $latestTag : null);

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
            'suggestedUpdateVersion' => $suggestedUpdateVersion,
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

        $version = $validated['version'] ?? null;

        $latestRelease = app(GitHubLatestReleaseService::class)->latest();
        $suggestedTag = app(GitHubReleasePublisher::class)->suggestNextTag($latestRelease['tag'] ?? null);
        $tag = (string) ($version ?: $suggestedTag);

        $normalizedTag = str_starts_with($tag, 'v') ? $tag : 'v'.$tag;

        $existing = SystemUpdate::query()->where('version', $normalizedTag)->first();

        if ($existing !== null) {
            return redirect()
                ->route('central.business-control.support-updates.index')
                ->with('status', "System update for {$normalizedTag} already exists.");
        }

        try {
            $release = app(GitHubReleasePublisher::class)->publish(
                tag: $normalizedTag,
                title: $validated['title'],
                summary: $validated['summary'] ?? null,
            );
        } catch (\Throwable $exception) {
            return redirect()
                ->route('central.business-control.support-updates.index')
                ->with('status', 'GitHub publish failed: '.$exception->getMessage());
        }

        SystemUpdate::query()->create([
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'version' => $release['tag'],
            'source' => 'github',
            'is_published' => (bool) ($validated['is_published'] ?? true),
            'published_at' => $validated['published_at'] ?? now(),
            'meta' => [
                'github' => [
                    'release_id' => $release['id'],
                    'html_url' => $release['html_url'],
                    'name' => $release['name'],
                    'published_at' => $release['published_at'],
                ],
            ],
            'created_by_super_admin_id' => auth('super_admin')->id(),
        ]);

        return redirect()
            ->route('central.business-control.support-updates.index')
            ->with('status', 'System update published.');
    }
}
