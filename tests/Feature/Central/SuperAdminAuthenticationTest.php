<?php

use App\Models\SuperAdmin;

test('central login screen can be rendered', function () {
    $response = $this->get(route('central.login'));

    $response->assertOk();
    $response->assertSee('Super Admin Sign In');
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
