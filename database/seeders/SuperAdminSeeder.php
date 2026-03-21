<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('central.bootstrap_super_admin.name');
        $email = config('central.bootstrap_super_admin.email');
        $password = config('central.bootstrap_super_admin.password');

        if (! is_string($name) || ! is_string($email) || ! is_string($password) || $name === '' || $email === '' || $password === '') {
            return;
        }

        SuperAdmin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
            ]
        );
    }
}
