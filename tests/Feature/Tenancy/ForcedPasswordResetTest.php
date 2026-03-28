<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

function forcedPasswordTenantDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'forced-password-tenant'.(string) config('tenancy.database.suffix');

    return database_path($databaseName);
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

    $databasePath = forcedPasswordTenantDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializeForcedPasswordTenant(): void
{
    $databasePath = forcedPasswordTenantDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'forced-password-tenant',
        'name' => 'Forced Password University',
        'plan' => 'basic',
        'status' => 'active',
        'expires_at' => now()->addDays(30),
    ]));

    tenancy()->initialize($tenant);

    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('student_player');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }
}

test('tenant user with temporary password is redirected to force-password page after login', function () {
    initializeForcedPasswordTenant();

    User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-admin@example.test',
        'role' => 'university_admin',
        'email_verified_at' => now(),
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
    ]);

    $response = $this->post(route('tenant.login.store'), [
        'email' => 'tenant-admin@example.test',
        'password' => 'TempPass123!',
    ]);

    $response->assertRedirect(route('tenant.force-password.edit'));
});

test('forced password update clears must_change_password flag', function () {
    initializeForcedPasswordTenant();

    $user = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-admin-update@example.test',
        'role' => 'university_admin',
        'email_verified_at' => now(),
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
    ]);

    $response = $this->actingAs($user)->put(route('tenant.force-password.update'), [
        'current_password' => 'TempPass123!',
        'password' => 'NewSecurePass123!',
        'password_confirmation' => 'NewSecurePass123!',
    ]);

    $response->assertRedirect(route('tenant.dashboard'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('NewSecurePass123!', $user->password))->toBeTrue();
});

test('tenant login is rejected when recaptcha verification fails', function () {
    initializeForcedPasswordTenant();

    config()->set('services.recaptcha.site_key', 'site-key');
    config()->set('services.recaptcha.secret_key', 'secret-key');
    config()->set('services.recaptcha.version', 'v3');
    config()->set('services.recaptcha.force_in_tests', true);

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
            'score' => 0.2,
            'action' => 'tenant_login',
        ], 200),
    ]);

    User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'tenant-admin-recaptcha@example.test',
        'role' => 'university_admin',
        'email_verified_at' => now(),
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
    ]);

    $response = $this->from(route('tenant.login'))->post(route('tenant.login.store'), [
        'email' => 'tenant-admin-recaptcha@example.test',
        'password' => 'TempPass123!',
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertRedirect(route('tenant.login'));
    $response->assertSessionHasErrors('recaptcha_token');
    $this->assertGuest('web');
});
