<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantSupportTicketRequest;
use App\Http\Requests\Tenant\UpdateTenantSettingsRequest;
use App\Models\SystemUpdate;
use App\Models\TenantSetting;
use App\Models\TenantSupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function edit(): View
    {
        $setting = TenantSetting::query()->firstWhere('tenant_id', tenant('id'));
        $privacyNotice = $this->privacyNotice();

        return view('tenant.settings.index', [
            'customization' => [
                'brand_primary_color' => (string) ($setting?->brand_primary_color ?? '#06b6d4'),
                'brand_secondary_color' => (string) ($setting?->brand_secondary_color ?? '#6366f1'),
                'theme_preference' => (string) ($setting?->theme_preference ?? 'system'),
            ],
            'privacyNotice' => $privacyNotice,
            'privacyNoticeSummary' => (string) ($privacyNotice['summary'] ?? ''),
            'privacyNoticeSections' => $privacyNotice['sections'] ?? [],
            'supportTickets' => TenantSupportTicket::query()
                ->where('tenant_id', tenant('id'))
                ->latest()
                ->limit(10)
                ->get(),
            'systemUpdates' => SystemUpdate::query()
                ->published()
                ->latest('published_at')
                ->latest('id')
                ->limit(10)
                ->get(),
            'tenantVersion' => (string) config('app.version', 'v1.0.0'),
        ]);
    }

    public function update(UpdateTenantSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        TenantSetting::query()->updateOrCreate([
            'tenant_id' => (string) tenant('id'),
        ], [
            'brand_primary_color' => $validated['brand_primary_color'],
            'brand_secondary_color' => $validated['brand_secondary_color'],
            'theme_preference' => $validated['theme_preference'],
        ]);

        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Tenant settings updated successfully.');
    }

    public function storeSupport(StoreTenantSupportTicketRequest $request): RedirectResponse
    {
        $user = $request->user();

        TenantSupportTicket::query()->create([
            'tenant_id' => (string) tenant('id'),
            'tenant_name' => (string) (tenant()?->name ?? 'Tenant'),
            'reported_by_user_id' => $user?->id,
            'reported_by_name' => (string) ($user?->name ?? 'Unknown User'),
            'reported_by_email' => (string) ($user?->email ?? 'unknown@example.test'),
            'reported_by_role' => (string) ($user?->role ?? 'unknown'),
            'category' => $request->validated('category'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'status' => 'open',
        ]);

        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Support report submitted to central support.');
    }

    /**
     * @return array{title: string, summary: string, sections: array<int, array{heading: string, content: string}>}
     */
    private function privacyNotice(): array
    {
        return [
            'title' => 'System Privacy Notice',
            'summary' => 'This notice is managed by the ISMS platform and applies to all tenant workspaces. Tenant admins cannot modify this policy text.',
            'sections' => [
                [
                    'heading' => 'Data We Collect',
                    'content' => 'The system stores account details, team rosters, match schedules, attendance responses, and audit logs needed to operate intramural sports workflows.',
                ],
                [
                    'heading' => 'Why Data Is Processed',
                    'content' => 'Data is processed to authenticate users, enforce role-based access, manage sports operations, generate standings, and support tenant-level reporting.',
                ],
                [
                    'heading' => 'Tenant Isolation and Security',
                    'content' => 'Each tenant workspace is isolated by tenant context and access controls. Users can only access records within their own tenant scope.',
                ],
                [
                    'heading' => 'Retention and Support Access',
                    'content' => 'Operational records remain available according to platform retention practices. Support reports are visible to authorized central administrators for issue resolution.',
                ],
            ],
        ];
    }
}
