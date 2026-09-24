<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DispatcherSeeder extends Seeder
{
    /**
     * Seed the default dispatcher account.
     */
    public function run(): void
    {
        $dispatcherRole = Role::query()->where('name', 'dispatcher')->firstOrFail();
        $attributes = [
            'username' => env('DEFAULT_DISPATCHER_USERNAME', 'Angela'),
            'password' => env('DEFAULT_DISPATCHER_PASSWORD', 'admin123'),
            'role_id' => $dispatcherRole->id,
            'is_active' => true,
        ];

        if (Schema::hasColumn('users', 'name')) {
            $attributes['name'] = env('DEFAULT_DISPATCHER_NAME', 'Angela Dispatcher');
        } else {
            $attributes['first_name'] = env('DEFAULT_DISPATCHER_FIRST_NAME', 'Angela');
            $attributes['last_name'] = env('DEFAULT_DISPATCHER_LAST_NAME', 'Dispatcher');
        }

        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_DISPATCHER_EMAIL', 'a@gmail.com')],
            $attributes,
        );
    }
}
