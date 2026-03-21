<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        User::query()->create([
            'name' => 'University Admin',
            'email' => 'admin@'.tenant('id').'.local',
            'role' => 'university_admin',
            'password' => Hash::make('password'),
        ]);
    }
}
