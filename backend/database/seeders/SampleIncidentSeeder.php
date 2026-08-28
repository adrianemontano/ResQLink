<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleIncidentSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::query()->where('email', 'admin@resqlink.local')->first();

        if ($reporter === null) {
            return;
        }

        foreach ([
            [
                'category' => 'Flood',
                'barangay' => 'Lahug',
                'latitude' => 10.3330,
                'longitude' => 123.8930,
                'impact_radius' => 50,
                'severity' => 'High',
                'status' => 'Reported',
                'notes' => 'TASK3 demo reported incident',
            ],
            [
                'category' => 'Fire',
                'barangay' => 'Capitol Site',
                'latitude' => 10.3168,
                'longitude' => 123.8912,
                'impact_radius' => 80,
                'severity' => 'Critical',
                'status' => 'Dispatched',
                'notes' => 'TASK3 demo dispatched incident',
            ],
        ] as $sample) {
            $categoryId = DB::table('incident_categories')->where('name', $sample['category'])->value('id');
            $severityId = DB::table('severity_levels')->where('name', $sample['severity'])->value('id');
            $statusId = DB::table('incident_statuses')->where('name', $sample['status'])->value('id');

            Incident::query()->updateOrCreate(
                ['notes' => $sample['notes']],
                [
                    ...$sample,
                    'reported_by' => $reporter->id,
                    'category_id' => $categoryId,
                    'severity_id' => $severityId,
                    'status_id' => $statusId,
                    'persons_count' => $sample['severity'] === 'Critical' ? 100 : 50,
                    'affected_population' => $sample['severity'] === 'Critical' ? 100 : 50,
                    'nearest_landmark' => $sample['barangay'].' area',
                    'landmark' => $sample['barangay'].' area',
                    'reported_at' => now()->subMinutes($sample['status'] === 'Dispatched' ? 25 : 10),
                ],
            );
        }
    }
}
