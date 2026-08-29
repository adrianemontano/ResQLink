<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SampleIncidentSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::query()->where('email', 'admin@resqlink.local')->first();
        $volunteer = User::query()->where('email', 'volunteer@resqlink.local')->first();

        if ($reporter === null || $volunteer === null) {
            return;
        }

        $incidentColumns = array_flip(Schema::getColumnListing('incidents'));

        foreach (['Lahug', 'Capitol Site'] as $barangay) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $barangay],
                ['created_at' => now(), 'updated_at' => now()],
            );
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
            $barangayId = DB::table('barangays')->where('name', $sample['barangay'])->value('id');
            $severityId = DB::table('severity_levels')->where('name', $sample['severity'])->value('id');
            $statusId = DB::table('incident_statuses')->where('name', $sample['status'])->value('id');

            if ($categoryId === null || $barangayId === null || $severityId === null || $statusId === null) {
                throw new \RuntimeException(
                    'Sample incident reference data is incomplete. Run RoleSeeder and ReferenceDataSeeder first.',
                );
            }

            $attributes = [
                    ...$sample,
                    'reported_by' => $reporter->id,
                    'volunteer_id' => $volunteer->id,
                    'category_id' => $categoryId,
                    'barangay_id' => $barangayId,
                    'severity_id' => $severityId,
                    'status_id' => $statusId,
                    'persons_count' => $sample['severity'] === 'Critical' ? 100 : 50,
                    'affected_population' => $sample['severity'] === 'Critical' ? 100 : 50,
                    'nearest_landmark' => $sample['barangay'].' area',
                    'landmark' => $sample['barangay'].' area',
                    'reported_at' => now()->subMinutes($sample['status'] === 'Dispatched' ? 25 : 10),
                ];

            $attributes = array_intersect_key($attributes, $incidentColumns);

            if (isset($incidentColumns['notes'])) {
                Incident::query()->updateOrCreate(
                    ['notes' => $sample['notes']],
                    $attributes,
                );
            } else {
                Incident::query()->create($attributes);
            }
        }
    }
}
