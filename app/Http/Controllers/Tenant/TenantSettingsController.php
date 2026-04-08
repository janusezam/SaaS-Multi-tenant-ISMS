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

        return view('tenant.settings.index', [
            'customization' => [
                'brand_primary_color' => (string) ($setting?->brand_primary_color ?? '#06b6d4'),
                'brand_secondary_color' => (string) ($setting?->brand_secondary_color ?? '#6366f1'),
                'theme_preference' => (string) ($setting?->theme_preference ?? 'system'),
            ],
            'privacyMessage' => (string) ($setting?->privacy_message ?? ''),
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
            'privacy_message' => (string) ($validated['privacy_message'] ?? ''),
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
}
