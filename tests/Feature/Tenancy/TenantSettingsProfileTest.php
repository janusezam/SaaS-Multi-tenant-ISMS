<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\TenantSetting;
use App\Models\TenantSupportTicket;
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
            'privacy_message' => 'This tenant protects athlete profile data and game activity logs.',
        ])
        ->assertRedirect(route('tenant.settings.edit'));

    $savedSettings = TenantSetting::query()->firstWhere('tenant_id', $tenant->id);
    $this->assertSame('#112233', $savedSettings?->brand_primary_color);

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
