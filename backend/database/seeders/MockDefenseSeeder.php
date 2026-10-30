<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MockDefenseSeeder extends Seeder
{
    /**
     * Seed comprehensive mock defense dataset:
     * - Reference data & roles
     * - Verified Barangay Responders (at least 8)
     * - 20+ incidents per category across all statuses and severities
     * - Full status transition histories
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ReferenceDataSeeder::class,
            AdminSeeder::class,
            DispatcherSeeder::class,
            VolunteerSeeder::class,
            MockDefenseResponderSeeder::class,
            MockDefenseIncidentSeeder::class,
        ]);
    }
}
