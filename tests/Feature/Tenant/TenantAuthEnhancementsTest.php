<?php

use App\Http\Middleware\EnsureTenantSubscriptionIsActive;
use App\Mail\TenantPasswordOtpMail;
use App\Mail\TenantRegistrationSubmittedMail;
use App\Mail\TenantUserInviteMail;
use App\Models\TenantUserRegistrationRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

beforeEach(function () {
    $this->withoutMiddleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        EnsureTenantSubscriptionIsActive::class,
    ]);

    if (! Schema::hasTable('tenant_user_registration_requests')) {
        Schema::create('tenant_user_registration_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 30);
            $table->string('role');
            $table->string('password');
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('tenant_password_reset_otps')) {
        Schema::create('tenant_password_reset_otps', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->index();
            $table->string('otp_hash', 64);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    Schema::table('users', function (Blueprint $table): void {
        if (! Schema::hasColumn('users', 'phone')) {
            $table->string('phone', 30)->nullable();
        }

        if (! Schema::hasColumn('users', 'must_change_password')) {
            $table->boolean('must_change_password')->default(false);
        }

        if (! Schema::hasColumn('users', 'invite_token_hash')) {
            $table->string('invite_token_hash', 64)->nullable();
        }

        if (! Schema::hasColumn('users', 'invite_expires_at')) {
            $table->timestamp('invite_expires_at')->nullable();
        }

        if (! Schema::hasColumn('users', 'invite_sent_at')) {
            $table->timestamp('invite_sent_at')->nullable();
        }

        if (! Schema::hasColumn('users', 'google_id')) {
            $table->string('google_id')->nullable();
        }

        if (! Schema::hasColumn('users', 'google_email')) {
            $table->string('google_email')->nullable();
        }
    });
});

test('tenant guest can submit self-registration request and notify tenant admins', function () {
    Mail::fake();

    User::factory()->create([
        'role' => 'university_admin',
        'email' => 'admin@tenant.test',
    ]);

    $response = $this->post(route('tenant.register.store'), [
        'name' => 'Coach Applicant',
        'email' => 'coach.applicant@tenant.test',
        'phone' => '09171234567',
        'role' => 'team_coach',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
    ]);

    $response->assertRedirect(route('tenant.login'));
    $response->assertSessionHas('status', 'Registration submitted. A tenant admin will review your account request.');

    $this->assertDatabaseHas('tenant_user_registration_requests', [
        'email' => 'coach.applicant@tenant.test',
        'status' => TenantUserRegistrationRequest::STATUS_PENDING,
    ]);

    Mail::assertQueued(TenantRegistrationSubmittedMail::class);
});

test('tenant admin can approve pending registration and send invite email', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'university_admin',
    ]);

    $registration = TenantUserRegistrationRequest::query()->create([
        'name' => 'Player Request',
        'email' => 'player.request@tenant.test',
        'phone' => '09998887777',
        'role' => 'student_player',
        'password' => Hash::make('InitialPass123!'),
        'status' => TenantUserRegistrationRequest::STATUS_PENDING,
    ]);

    $response = $this->actingAs($admin)->post(route('tenant.users.pending.approve', $registration));

    $response->assertRedirect(route('tenant.users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'player.request@tenant.test',
        'role' => 'student_player',
    ]);

    $this->assertDatabaseHas('tenant_user_registration_requests', [
        'id' => $registration->id,
        'status' => TenantUserRegistrationRequest::STATUS_APPROVED,
    ]);

    Mail::assertQueued(TenantUserInviteMail::class);
});

test('tenant user can reset password using otp email flow', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'otp.user@tenant.test',
        'password' => 'OldPassword123!',
    ]);

    $response = $this->post(route('tenant.password.otp.send'), [
        'email' => 'otp.user@tenant.test',
    ]);

    $response->assertRedirect(route('tenant.password.otp.reset-form', ['email' => 'otp.user@tenant.test']));

    $otpCode = null;

    Mail::assertQueued(TenantPasswordOtpMail::class, function (TenantPasswordOtpMail $mail) use (&$otpCode): bool {
        $otpCode = $mail->otpCode;

        return true;
    });

    expect($otpCode)->not()->toBeNull();

    $resetResponse = $this->post(route('tenant.password.otp.reset'), [
        'email' => 'otp.user@tenant.test',
        'otp' => $otpCode,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $resetResponse->assertRedirect(route('tenant.login'));

    $user->refresh();

    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();

    $record = DB::table('tenant_password_reset_otps')->where('email', 'otp.user@tenant.test')->orderByDesc('id')->first();

    expect($record)->not()->toBeNull();
    expect($record?->consumed_at)->not()->toBeNull();
});

test('google callback signs in existing tenant user only', function () {
    config()->set('services.google.enabled', true);
    config()->set('services.google.client_id', 'test-client');
    config()->set('services.google.client_secret', 'test-secret');

    $tenantUser = User::factory()->create([
        'email' => 'google.user@tenant.test',
        'role' => 'team_coach',
    ]);

    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => 'google-123',
        'email' => 'google.user@tenant.test',
        'name' => 'Google User',
    ]);

    Socialite::shouldReceive('driver->redirectUrl->stateless->user')->andReturn($socialiteUser);

    $response = $this->get(route('tenant.login.google.callback'));

    $response->assertRedirect(route('tenant.dashboard', absolute: false));

    $tenantUser->refresh();

    expect($tenantUser->google_id)->toBe('google-123');
    expect((string) $tenantUser->google_email)->toBe('google.user@tenant.test');
    $this->assertAuthenticatedAs($tenantUser);
});

test('google callback rejects unknown tenant email', function () {
    config()->set('services.google.enabled', true);
    config()->set('services.google.client_id', 'test-client');
    config()->set('services.google.client_secret', 'test-secret');

    $socialiteUser = new SocialiteUser;
    $socialiteUser->map([
        'id' => 'google-999',
        'email' => 'unknown@tenant.test',
        'name' => 'Unknown User',
    ]);

    Socialite::shouldReceive('driver->redirectUrl->stateless->user')->andReturn($socialiteUser);

    $response = $this->from(route('tenant.login'))->get(route('tenant.login.google.callback'));

    $response->assertRedirect(route('tenant.login'));
    $response->assertSessionHasErrors('email');
});

test('google redirect uses tenant callback url', function () {
    config()->set('services.google.enabled', true);
    config()->set('services.google.client_id', 'test-client');
    config()->set('services.google.client_secret', 'test-secret');

    Socialite::shouldReceive('driver->redirectUrl->scopes->redirect')
        ->once()
        ->andReturn(redirect('/oauth/google'));

    $response = $this->get(route('tenant.login.google.redirect'));

    $response->assertRedirect('/oauth/google');
});
