<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\SystemUpdate;
use App\Models\TenantSetting;
use App\Models\TenantSupportTicket;
use App\Models\TenantSystemUpdateRead;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

function tenantSettingsDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'tenant-settings-test'.(string) config('tenancy.database.suffix');

    return database_path($databaseName);
}

function initializeTenantSettingsContext(): University
{
    $databasePath = tenantSettingsDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'tenant-settings-test',
        'name' => 'Tenant Settings University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
        'data' => [],
    ]));

    tenancy()->initialize($tenant);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('position_title', 120)->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->string('role')->default('student_player');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    return $tenant;
}

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);
});

afterEach(function () {
    if (tenant() !== null) {
        tenancy()->end();
    }

    $databasePath = tenantSettingsDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

test('tenant can update customization settings and submit support report', function () {
    $tenant = initializeTenantSettingsContext();

    TenantSetting::query()->create([
        'tenant_id' => $tenant->id,
        'privacy_message' => 'Legacy tenant-managed message',
    ]);

    $user = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-settings-admin@example.test',
        'role' => 'university_admin',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('tenant.settings.update'), [
            'brand_primary_color' => '#112233',
            'brand_secondary_color' => '#445566',
            'theme_preference' => 'dark',
            'use_custom_theme' => true,
            'privacy_message' => 'This tenant protects athlete profile data and game activity logs.',
        ])
        ->assertRedirect(route('tenant.settings.edit'));

    $savedSettings = TenantSetting::query()->firstWhere('tenant_id', $tenant->id);
    $this->assertSame('#112233', $savedSettings?->brand_primary_color);
    $this->assertSame('Legacy tenant-managed message', $savedSettings?->privacy_message);

    $this->actingAs($user)
        ->get(route('tenant.settings.edit'))
        ->assertOk()
        ->assertSee('data-app-context="tenant"', false)
        ->assertSee("classList.toggle('dark'", false)
        ->assertSee('--isms-brand-primary-accent: #112233;')
        ->assertSee('--isms-brand-primary-on: #f8fafc;')
        ->assertSee('--isms-brand-primary-shadow: rgba(17, 34, 51, 0.26);')
        ->assertSee('--isms-brand-secondary-accent: #445566;')
        ->assertSee('--isms-brand-secondary-on: #f8fafc;')
        ->assertSee('--isms-brand-secondary-shadow: rgba(68, 85, 102, 0.26);');

    $this->actingAs($user)
        ->post(route('tenant.settings.support.store'), [
            'category' => 'bug',
            'subject' => 'Lineup page issue',
            'message' => 'Submitting lineup fails after selecting players.',
        ])
        ->assertRedirect(route('tenant.settings.edit'));

    expect(
        TenantSupportTicket::query()
            ->where('tenant_id', 'tenant-settings-test')
            ->where('subject', 'Lineup page issue')
            ->where('status', 'open')
            ->exists()
    )->toBeTrue();
});

test('light tenant brand colors expose readable on-color variables', function () {
    initializeTenantSettingsContext();

    $user = User::query()->create([
        'name' => 'Light Palette Admin',
        'email' => 'tenant-light-palette-admin@example.test',
        'role' => 'university_admin',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('tenant.settings.update'), [
            'brand_primary_color' => '#fef08a',
            'brand_secondary_color' => '#fde68a',
            'theme_preference' => 'light',
            'use_custom_theme' => true,
        ])
        ->assertRedirect(route('tenant.settings.edit'));

    $this->actingAs($user)
        ->get(route('tenant.settings.edit'))
        ->assertOk()
        ->assertSee('--isms-brand-primary-accent: #0f172a;')
        ->assertSee('--isms-brand-primary-on: #0f172a;')
        ->assertSee('--isms-brand-secondary-accent: #0f172a;')
        ->assertSee('--isms-brand-secondary-on: #0f172a;');
});

test('tenant can save brand colors without applying them', function () {
    $tenant = initializeTenantSettingsContext();

    $user = User::query()->create([
        'name' => 'Tenant Custom Theme Saver',
        'email' => 'tenant-custom-theme-saver@example.test',
        'role' => 'university_admin',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('tenant.settings.update'), [
            'brand_primary_color' => '#a855f7',
            'brand_secondary_color' => '#f97316',
            'theme_preference' => 'system',
            'use_custom_theme' => false,
        ])
        ->assertRedirect(route('tenant.settings.edit'));

    $savedSettings = TenantSetting::query()->firstWhere('tenant_id', $tenant->id);
    $this->assertSame('#a855f7', $savedSettings?->brand_primary_color);
    $this->assertSame('#f97316', $savedSettings?->brand_secondary_color);
    $this->assertFalse((bool) ($savedSettings?->use_custom_theme ?? true));

    $this->actingAs($user)
        ->get(route('tenant.settings.edit'))
        ->assertOk()
        ->assertSee('data-app-context="tenant"', false)
        ->assertDontSee('--isms-brand-primary:', false)
        ->assertDontSee('--isms-brand-secondary:', false);
});

test('tenant can update profile details', function () {
    initializeTenantSettingsContext();

    $user = User::query()->create([
        'name' => 'Coach One',
        'email' => 'tenant-profile-user@example.test',
        'role' => 'team_coach',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('tenant.profile.update'), [
            'name' => 'Coach Updated',
            'phone' => '+63 900 000 1111',
            'bio' => 'Focuses on defense and team discipline.',
            'current_password' => 'password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])
        ->assertRedirect(route('tenant.profile.edit'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Coach Updated',
        'phone' => '+63 900 000 1111',
    ]);

    $updatedUser = $user->fresh();
    expect(Hash::check('new-strong-password', (string) $updatedUser?->password))->toBeTrue();
});

test('tenant only sees published system updates and can mark as read', function () {
    $tenant = initializeTenantSettingsContext();

    $user = User::query()->create([
        'name' => 'Tenant Updates Viewer',
        'email' => 'tenant-updates-viewer@example.test',
        'role' => 'university_admin',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $publishedUpdate = SystemUpdate::query()->create([
        'title' => 'Release Notes v1.2.0',
        'summary' => 'New planning tools and bug fixes.',
        'version' => 'v1.2.0',
        'source' => 'manual',
        'is_published' => true,
        'published_at' => now()->subMinute(),
    ]);

    SystemUpdate::query()->create([
        'title' => 'Draft Internal Update',
        'summary' => 'Should not be visible to tenant.',
        'version' => 'v1.2.1',
        'source' => 'manual',
        'is_published' => false,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('tenant.settings.edit'))
        ->assertOk()
        ->assertSee('Release Notes v1.2.0')
        ->assertDontSee('Draft Internal Update')
        ->assertSee('New');

    $this->actingAs($user)
        ->post(route('tenant.settings.updates.read', $publishedUpdate))
        ->assertRedirect(route('tenant.settings.edit'))
        ->assertSessionHas('status', 'System update marked as read.');

    expect(TenantSystemUpdateRead::query()
        ->where('system_update_id', $publishedUpdate->id)
        ->where('tenant_id', $tenant->id)
        ->where('tenant_user_id', $user->id)
        ->exists())->toBeTrue();

    $this->actingAs($user)
        ->get(route('tenant.settings.edit'))
        ->assertOk()
        ->assertSee('Read')
        ->assertDontSee('Mark as read');
});

test('tenant cannot mark unpublished update as read', function () {
    initializeTenantSettingsContext();

    $user = User::query()->create([
        'name' => 'Tenant Updates Guard',
        'email' => 'tenant-updates-guard@example.test',
        'role' => 'university_admin',
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $draftUpdate = SystemUpdate::query()->create([
        'title' => 'Draft Release',
        'source' => 'manual',
        'is_published' => false,
    ]);

    $this->actingAs($user)
        ->post(route('tenant.settings.updates.read', $draftUpdate))
        ->assertNotFound();

    expect(TenantSystemUpdateRead::query()->count())->toBe(0);
});
