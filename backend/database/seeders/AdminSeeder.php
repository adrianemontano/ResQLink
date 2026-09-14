<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Seed the default administrator account.
     */
    public function run(): void
    {
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $attributes = [
            'username' => env('DEFAULT_ADMIN_USERNAME', 'admin'),
            'password' => env('DEFAULT_ADMIN_PASSWORD', 'Admin@12345'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ];

        if (Schema::hasColumn('users', 'name')) {
            $attributes['name'] = env('DEFAULT_ADMIN_NAME', 'System Administrator');
        } else {
            $attributes['first_name'] = 'System';
            $attributes['last_name'] = 'Administrator';
        }

        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_ADMIN_EMAIL', 'admin@resqlink.local')],
            $attributes,
        );
    }
}
