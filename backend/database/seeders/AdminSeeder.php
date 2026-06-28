<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the default administrator account.
     */
    public function run(): void
    {
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_ADMIN_EMAIL', 'admin@resqlink.local')],
            [
                'name' => env('DEFAULT_ADMIN_NAME', 'System Administrator'),
                'username' => env('DEFAULT_ADMIN_USERNAME', 'admin'),
                'password' => env('DEFAULT_ADMIN_PASSWORD', 'Admin@12345'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ],
        );
    }
}
