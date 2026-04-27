<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantSupportTicketRequest;
use App\Http\Requests\Tenant\UpdateTenantSettingsRequest;
use App\Models\SystemUpdate;
use App\Models\TenantSetting;
use App\Models\TenantSupportTicket;
use App\Models\TenantSystemUpdateRead;
use App\Services\GitHubLatestReleaseService;
use App\Services\SelfUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function edit(): View
    {
        $setting = TenantSetting::query()->firstWhere('tenant_id', tenant('id'));
        $privacyNotice = $this->privacyNotice();
        $tenantId = (string) tenant('id');
        $tenantUserId = auth()->id();
        $systemUpdates = SystemUpdate::query()
            ->published()
            ->where('source', 'github')
            ->latest('published_at')
            ->latest('id')
            ->limit(10)
            ->get();

        $updateIds = $systemUpdates->pluck('id')->all();
        $readUpdateIds = TenantSystemUpdateRead::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('system_update_id', $updateIds)
            ->where(function ($query) use ($tenantUserId): void {
                $query
                    ->whereNull('tenant_user_id')
                    ->orWhere('tenant_user_id', $tenantUserId);
            })
            ->pluck('system_update_id')
            ->all();

        $tenantVersion = (string) config('app.version', 'v1.0.0');
        $latestRelease = app(GitHubLatestReleaseService::class)->latest();
        $latestTag = (string) ($latestRelease['tag'] ?? '');
        $updateAvailable = $latestTag !== ''
            && version_compare(ltrim($latestTag, 'v'), ltrim($tenantVersion, 'v'), '>');

        return view('tenant.settings.index', [
            'customization' => [
                'brand_primary_color' => (string) ($setting?->brand_primary_color ?? '#06b6d4'),
                'brand_secondary_color' => (string) ($setting?->brand_secondary_color ?? '#6366f1'),
                'theme_preference' => (string) ($setting?->theme_preference ?? 'system'),
                'use_custom_theme' => (bool) ($setting?->use_custom_theme ?? false),
                'branding_logo_path' => $setting?->branding_logo_path,
                'login_brand_badge' => (string) ($setting?->login_brand_badge ?? 'Your School Operations Hub'),
                'login_brand_heading' => (string) ($setting?->login_brand_heading ?? 'Sign in to your intramurals workspace'),
                'login_brand_description' => (string) ($setting?->login_brand_description ?? 'Access events, teams, fixtures, game results, and standings in one SaaS platform built for university sports programs.'),
                'login_brand_feature_1' => (string) ($setting?->login_brand_feature_1 ?? 'Role-based access for admins, facilitators, and staff'),
                'login_brand_feature_2' => (string) ($setting?->login_brand_feature_2 ?? 'Real-time scheduling and score tracking'),
                'login_brand_feature_3' => (string) ($setting?->login_brand_feature_3 ?? 'Plan-gated analytics, brackets, and exports'),
            ],
            'privacyNotice' => $privacyNotice,
            'privacyNoticeSummary' => (string) ($privacyNotice['summary'] ?? ''),
            'privacyNoticeSections' => $privacyNotice['sections'] ?? [],
            'supportTickets' => TenantSupportTicket::query()
                ->where('tenant_id', tenant('id'))
                ->latest()
                ->limit(10)
                ->get(),
            'systemUpdates' => $systemUpdates,
            'readUpdateIds' => $readUpdateIds,
            'tenantVersion' => $tenantVersion,
            'latestRelease' => $latestRelease,
            'updateAvailable' => $updateAvailable,
            'selfUpdateInProgress' => app(SelfUpdateService::class)->isUpdateInProgress(),
        ]);
    }

    public function startSelfUpdate(Request $request, SelfUpdateService $selfUpdateService): RedirectResponse
    {
        if (! app()->environment(['local', 'testing'])) {
            abort(404);
        }

        $error = $selfUpdateService->preflightError();

        if ($error !== null) {
            return redirect()
                ->route('tenant.settings.edit')
                ->with('status', $error);
        }

        // Set the "in progress" flag before we send the response
        $selfUpdateService->markInProgress();

        // Build the redirect response
        $response = redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Update started. Refresh in a few minutes.');

        // Send the response to the browser immediately
        $response->send();

        // Ensure PHP continues running even after the browser disconnects
        ignore_user_abort(true);
        set_time_limit(0);

        // Close the session so it doesn't block other requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Flush all output buffers so the browser gets the response right away
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            flush();
        }

        // Now run the update synchronously — no child process, no risk of being killed
        Artisan::call('app:self-update');

        exit(0);
    }

    public function markUpdateAsRead(SystemUpdate $update): RedirectResponse
    {
        if (! $update->is_published || ($update->published_at !== null && $update->published_at->isFuture())) {
            abort(404);
        }

        TenantSystemUpdateRead::query()->firstOrCreate([
            'system_update_id' => $update->id,
            'tenant_id' => (string) tenant('id'),
            'tenant_user_id' => auth()->id(),
        ], [
            'read_at' => now(),
        ]);

        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'System update marked as read.');
    }

    public function update(UpdateTenantSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tenantId = (string) tenant('id');
        $setting = TenantSetting::query()->firstWhere('tenant_id', $tenantId);
        $isUniversityAdmin = $request->user()?->hasTenantRole('university_admin') === true;
        $settingsSection = (string) ($validated['settings_section'] ?? 'all');
        $updatingTheme = in_array($settingsSection, ['theme_brand', 'all'], true);
        $updatingBranding = in_array($settingsSection, ['branding', 'all'], true);

        $attributes = [];

        if ($updatingTheme) {
            $attributes['brand_primary_color'] = $validated['brand_primary_color'];
            $attributes['brand_secondary_color'] = $validated['brand_secondary_color'];
            $attributes['theme_preference'] = $validated['theme_preference'];
            $attributes['use_custom_theme'] = (bool) ($validated['use_custom_theme'] ?? false);
        }

        if ($updatingBranding && $isUniversityAdmin) {
            $attributes['login_brand_badge'] = $this->nullableTrim($validated['login_brand_badge'] ?? null);
            $attributes['login_brand_heading'] = $this->nullableTrim($validated['login_brand_heading'] ?? null);
            $attributes['login_brand_description'] = $this->nullableTrim($validated['login_brand_description'] ?? null);
            $attributes['login_brand_feature_1'] = $this->nullableTrim($validated['login_brand_feature_1'] ?? null);
            $attributes['login_brand_feature_2'] = $this->nullableTrim($validated['login_brand_feature_2'] ?? null);
            $attributes['login_brand_feature_3'] = $this->nullableTrim($validated['login_brand_feature_3'] ?? null);

            if (($validated['remove_branding_logo'] ?? false) && $setting?->branding_logo_path !== null) {
                Storage::disk('public')->delete((string) $setting->branding_logo_path);
                $attributes['branding_logo_path'] = null;
            }

            if ($request->hasFile('branding_logo')) {
                if ($setting?->branding_logo_path !== null) {
                    Storage::disk('public')->delete((string) $setting->branding_logo_path);
                }

                $attributes['branding_logo_path'] = $request->file('branding_logo')->store(
                    'tenants/'.$tenantId.'/branding',
                    'public'
                );
            }
        }

        if ($attributes === []) {
            return redirect()
                ->route('tenant.settings.edit')
                ->with('status', 'No settings changes submitted.');
        }

        TenantSetting::query()->updateOrCreate([
            'tenant_id' => $tenantId,
        ], $attributes);

        return redirect()
            ->route('tenant.settings.edit')
            ->with('status', 'Tenant settings updated successfully.');
    }

    private function nullableTrim(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
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
