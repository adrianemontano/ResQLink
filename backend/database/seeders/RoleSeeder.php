<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'System administrator', 'slug' => 'admin'],
            ['name' => 'dispatcher', 'description' => 'Incident dispatcher', 'slug' => 'dispatcher'],
            ['name' => 'volunteer', 'description' => 'Community responder', 'slug' => 'volunteer'],
        ];

        foreach ($roles as $role) {
            if (! Schema::hasColumn('roles', 'slug')) {
                unset($role['slug']);
            }

            Role::query()->updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
