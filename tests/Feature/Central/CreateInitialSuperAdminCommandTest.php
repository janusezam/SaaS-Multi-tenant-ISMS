<?php

use App\Models\SuperAdmin;

test('super admin bootstrap command creates account', function () {
    $this->artisan('app:super-admin:create', [
        '--name' => 'Bootstrap Admin',
        '--email' => 'bootstrap-admin@example.test',
        '--password' => 'bootstrap-password',
    ])->assertSuccessful();

    $this->assertDatabaseHas('super_admins', [
        'email' => 'bootstrap-admin@example.test',
        'name' => 'Bootstrap Admin',
    ]);
});

test('super admin bootstrap command prevents duplicate email without update flag', function () {
    SuperAdmin::query()->create([
        'name' => 'Existing Admin',
        'email' => 'existing-admin@example.test',
        'password' => 'original-password',
    ]);

    $this->artisan('app:super-admin:create', [
        '--name' => 'Changed Name',
        '--email' => 'existing-admin@example.test',
        '--password' => 'new-password',
    ])->assertFailed();

    $this->assertDatabaseHas('super_admins', [
        'email' => 'existing-admin@example.test',
        'name' => 'Existing Admin',
    ]);
});

test('super admin bootstrap command updates existing account with update flag', function () {
    SuperAdmin::query()->create([
        'name' => 'Old Admin',
        'email' => 'updatable-admin@example.test',
        'password' => 'old-password',
    ]);

    $this->artisan('app:super-admin:create', [
        '--name' => 'Updated Admin',
        '--email' => 'updatable-admin@example.test',
        '--password' => 'new-password',
        '--update' => true,
    ])->assertSuccessful();

    $admin = SuperAdmin::query()->where('email', 'updatable-admin@example.test')->firstOrFail();

    expect($admin->name)->toBe('Updated Admin');
    expect(password_verify('new-password', $admin->password))->toBeTrue();
});
