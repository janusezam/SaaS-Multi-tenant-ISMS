<?php

test('registration screen is disabled', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('registration endpoint is disabled', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertNotFound();
});
