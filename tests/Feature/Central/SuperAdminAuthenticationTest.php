<?php

use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Http;

test('central login screen can be rendered', function () {
    $response = $this->get(route('central.login'));

    $response->assertOk();
    $response->assertSee('Super Admin Access');
});

test('super admins can authenticate using central login', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'auth-super-admin@example.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('central.login.store'), [
        'email' => $superAdmin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('super_admin');
    $response->assertRedirect(route('central.universities.index', absolute: false));
});

test('authenticated super admins are redirected away from central login screen', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'auth-super-admin-redirect@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('central.login'));

    $response->assertRedirect(route('central.universities.index'));
});

test('authenticated super admins are redirected to central dashboard when hitting shared login route', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'auth-super-admin-shared-login@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->get(route('login'));

    $response->assertRedirect(route('central.universities.index'));
});

test('super admins can logout from central app', function () {
    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'auth-super-admin-logout@example.test',
        'password' => 'password',
    ]);

    $response = $this->actingAs($superAdmin, 'super_admin')->post(route('central.logout'));

    $this->assertGuest('super_admin');
    $response->assertRedirect(route('central.login'));
});

test('central login is rejected when recaptcha verification fails', function () {
    config()->set('services.recaptcha.site_key', 'site-key');
    config()->set('services.recaptcha.secret_key', 'secret-key');
    config()->set('services.recaptcha.version', 'v3');
    config()->set('services.recaptcha.force_in_tests', true);

    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
            'score' => 0.1,
            'action' => 'central_login',
        ], 200),
    ]);

    $superAdmin = SuperAdmin::query()->create([
        'name' => 'Central Admin',
        'email' => 'auth-super-admin-recaptcha@example.test',
        'password' => 'password',
    ]);

    $response = $this->from(route('central.login'))->post(route('central.login.store'), [
        'email' => $superAdmin->email,
        'password' => 'password',
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertRedirect(route('central.login'));
    $response->assertSessionHasErrors('recaptcha_token');
    $this->assertGuest('super_admin');
});
