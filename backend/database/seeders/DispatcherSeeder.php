<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DispatcherSeeder extends Seeder
{
    /**
     * Seed the default dispatcher account.
     */
    public function run(): void
    {
        $dispatcherRole = Role::query()->where('name', 'dispatcher')->firstOrFail();

        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_DISPATCHER_EMAIL', 'a@gmail.com')],
            [
                'first_name' => env('DEFAULT_DISPATCHER_FIRST_NAME', 'Angela'),
                'last_name' => env('DEFAULT_DISPATCHER_LAST_NAME', 'Dispatcher'),
                'username' => env('DEFAULT_DISPATCHER_USERNAME', 'Angela'),
                'password' => env('DEFAULT_DISPATCHER_PASSWORD', 'admin123'),
                'role_id' => $dispatcherRole->id,
                'is_active' => true,
            ],
        );
    }
}
