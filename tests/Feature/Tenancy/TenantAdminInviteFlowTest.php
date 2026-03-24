<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

function tenantAdminInviteDatabasePath(): string
{
    $databaseName = (string) config('tenancy.database.prefix').'tenantinvite-tenant'.(string) config('tenancy.database.suffix');

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

    $databasePath = tenantAdminInviteDatabasePath();

    if (is_file($databasePath)) {
        @unlink($databasePath);
    }
});

function initializeTenantAdminInviteTenant(): void
{
    $databasePath = tenantAdminInviteDatabasePath();

    if (! is_file($databasePath)) {
        touch($databasePath);
    }

    $tenant = University::withoutEvents(fn () => University::query()->create([
        'id' => 'tenantinvite-tenant',
        'name' => 'Tenant Invite University',
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
            $table->string('invite_token_hash', 64)->nullable();
            $table->timestamp('invite_expires_at')->nullable();
            $table->timestamp('invite_sent_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
}

test('tenant admin invite link shows password setup page for valid token', function () {
    initializeTenantAdminInviteTenant();

    $plainToken = 'valid-token-123';

    User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'invite-admin@example.test',
        'role' => 'university_admin',
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
        'invite_token_hash' => hash('sha256', $plainToken),
        'invite_expires_at' => now()->addDay(),
        'invite_sent_at' => now(),
    ]);

    $response = $this->get(route('tenant.admin-invite.edit', ['token' => $plainToken, 'email' => 'invite-admin@example.test']));

    $response->assertOk();
    $response->assertSee('Set Tenant Admin Password');
});

test('tenant admin can set password with valid invite token', function () {
    initializeTenantAdminInviteTenant();

    $plainToken = 'another-valid-token-123';

    $user = User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'invite-update@example.test',
        'role' => 'university_admin',
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
        'invite_token_hash' => hash('sha256', $plainToken),
        'invite_expires_at' => now()->addDay(),
        'invite_sent_at' => now(),
    ]);

    $response = $this->post(route('tenant.admin-invite.update'), [
        'token' => $plainToken,
        'email' => 'invite-update@example.test',
        'password' => 'NewStrongPass123!',
        'password_confirmation' => 'NewStrongPass123!',
    ]);

    $response->assertRedirect(route('tenant.login'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();
    expect($user->invite_token_hash)->toBeNull();
    expect($user->invite_expires_at)->toBeNull();
    expect(Hash::check('NewStrongPass123!', $user->password))->toBeTrue();
});

test('invalid or expired invite token is rejected', function () {
    initializeTenantAdminInviteTenant();

    User::query()->create([
        'name' => 'Tenant Admin',
        'email' => 'invite-expired@example.test',
        'role' => 'university_admin',
        'password' => Hash::make('TempPass123!'),
        'must_change_password' => true,
        'invite_token_hash' => hash('sha256', 'expired-token'),
        'invite_expires_at' => now()->subMinute(),
        'invite_sent_at' => now()->subDay(),
    ]);

    $response = $this->get(route('tenant.admin-invite.edit', ['token' => 'expired-token', 'email' => 'invite-expired@example.test']));

    $response->assertNotFound();
});
