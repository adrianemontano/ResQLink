<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ([
            ['name' => 'Flood', 'description' => 'Flooding incident'],
            ['name' => 'Earthquake', 'description' => 'Earthquake incident'],
            ['name' => 'Landslide', 'description' => 'Landslide incident'],
            ['name' => 'Fire', 'description' => 'Fire incident'],
        ] as $category) {
            DB::table('incident_categories')->updateOrInsert(
                ['name' => $category['name']],
                [...$category, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach ([
            ['name' => 'Low', 'sort_order' => 1],
            ['name' => 'Moderate', 'sort_order' => 2],
            ['name' => 'High', 'sort_order' => 3],
            ['name' => 'Critical', 'sort_order' => 4],
        ] as $severity) {
            DB::table('severity_levels')->updateOrInsert(
                ['name' => $severity['name']],
                [...$severity, 'description' => null, 'created_at' => $now, 'updated_at' => $now],
            );
        }

        foreach (['Reported', 'Received', 'Dispatched', 'Completed'] as $order => $name) {
            DB::table('incident_statuses')->updateOrInsert(
                ['name' => $name],
                ['sort_order' => $order + 1, 'description' => null, 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }
}
