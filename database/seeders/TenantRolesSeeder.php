<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::query()->where('role', 'university_admin')->exists()) {
            return;
        }

        $tenant = tenant();

        $adminName = $tenant?->tenant_admin_name ?? 'University Admin';
        $adminEmail = $tenant?->tenant_admin_email ?? ('admin+'.($tenant?->id ?? 'tenant').'@example.invalid');

        $attributes = [
            'name' => $adminName,
            'email' => $adminEmail,
            'role' => 'university_admin',
            'password' => Str::random(40),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $attributes['must_change_password'] = true;
        }

        User::query()->create($attributes);
    }
}
