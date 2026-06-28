<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Dispatcher', 'slug' => 'dispatcher'],
            ['name' => 'Volunteer', 'slug' => 'volunteer'],
        ] as $role) {
            Role::query()->updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
