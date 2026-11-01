<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MockDefenseIncidentSeeder extends Seeder
{
    /**
     * Seed 88 realistic incidents with complete status transition histories.
     */
    public function run(): void
    {
        $responders = User::query()->whereRelation('role', 'name', 'volunteer')->get();
        if ($responders->isEmpty()) {
            $this->call(MockDefenseResponderSeeder::class);
            $responders = User::query()->whereRelation('role', 'name', 'volunteer')->get();
        }

        $dispatcher = User::query()->whereRelation('role', 'name', 'dispatcher')->first()
            ?? User::query()->whereRelation('role', 'name', 'admin')->first();

        $incidentColumns = array_flip(Schema::getColumnListing('incidents'));
        $data = MockDefenseIncidentData::get();
        $responderCount = $responders->count();
        $counter = 0;

        foreach ($data as $category => $samples) {
            $categoryId = DB::table('incident_categories')->where('name', $category)->value('id');

            foreach ($samples as $sample) {
                $counter++;
                $responder = $responders[$counter % $responderCount];

                DB::table('barangays')->updateOrInsert(
                    ['name' => $sample['barangay']],
                    ['created_at' => now(), 'updated_at' => now()],
                );

                $barangayId = DB::table('barangays')->where('name', $sample['barangay'])->value('id');
                $severityId = DB::table('severity_levels')->where('name', $sample['severity'])->value('id');
                $statusId = DB::table('incident_statuses')->where('name', $sample['status'])->value('id');

                [$population, $radius] = match ($sample['severity']) {
                    'Critical' => [150, 750.0],
                    'High' => [75, 250.0],
                    'Moderate' => [30, 85.0],
                    default => [6, 45.0],
                };

                // Space reports across the last 3 days
                $hoursAgo = 72 - (int) (($counter / 88.0) * 70);
                $reportedAt = Carbon::now()->subHours($hoursAgo)->subMinutes(($counter * 7) % 60);

                $dispatchedAt = in_array($sample['status'], ['Dispatched', 'Completed'], true)
                    ? $reportedAt->copy()->addMinutes(25)
                    : null;

                $completedAt = $sample['status'] === 'Completed'
                    ? $reportedAt->copy()->addMinutes(110)
                    : null;

                $attributes = [
                    'category' => $category,
                    'category_id' => $categoryId,
                    'barangay' => $sample['barangay'],
                    'barangay_id' => $barangayId,
                    'severity' => $sample['severity'],
                    'severity_id' => $severityId,
                    'status' => $sample['status'],
                    'status_id' => $statusId,
                    'latitude' => $sample['lat'],
                    'longitude' => $sample['lng'],
                    'nearest_landmark' => $sample['landmark'],
                    'landmark' => $sample['landmark'],
                    'affected_population' => $population,
                    'persons_count' => $population,
                    'impact_radius' => $radius,
                    'notes' => $sample['notes'],
                    'reported_by' => $responder->id,
                    'volunteer_id' => $responder->id,
                    'reported_at' => $reportedAt,
                    'dispatched_at' => $dispatchedAt,
                    'completed_at' => $completedAt,
                ];

                $attributes = array_intersect_key($attributes, $incidentColumns);

                $incident = Incident::query()->updateOrCreate(
                    [
                        'category' => $category,
                        'nearest_landmark' => $sample['landmark'],
                    ],
                    $attributes,
                );

                $this->seedTransitions($incident, $sample['status'], $responder, $dispatcher, $reportedAt);
            }
        }

        // Ensure any pre-existing incidents without history also have full transition trails
        $legacyIncidents = Incident::query()->doesntHave('history')->get();
        foreach ($legacyIncidents as $legacy) {
            $resp = $legacy->reporter ?? $responders->first();
            $repAt = $legacy->reported_at ? Carbon::parse($legacy->reported_at) : Carbon::now()->subDays(2);
            $this->seedTransitions($legacy, $legacy->status ?? 'Reported', $resp, $dispatcher, $repAt);
        }
    }

    /**
     * Create realistic chronological status history transitions for the incident.
     */
    private function seedTransitions(
        Incident $incident,
        string $targetStatus,
        User $responder,
        ?User $dispatcher,
        Carbon $reportedAt,
    ): void {
        $historyStages = [
            'Reported' => [
                'status' => 'Reported',
                'user_id' => $responder->id,
                'time' => $reportedAt,
                'note' => 'Initial emergency report submitted by Barangay Responder.',
            ],
            'Received' => [
                'status' => 'Received',
                'user_id' => $dispatcher?->id ?? $responder->id,
                'time' => $reportedAt->copy()->addMinutes(8),
                'note' => 'Dispatcher verified incident coordinates and logged incoming report.',
            ],
            'Dispatched' => [
                'status' => 'Dispatched',
                'user_id' => $dispatcher?->id ?? $responder->id,
                'time' => $reportedAt->copy()->addMinutes(25),
                'note' => 'Emergency response unit and field team dispatched to location.',
            ],
            'Completed' => [
                'status' => 'Completed',
                'user_id' => $dispatcher?->id ?? $responder->id,
                'time' => $reportedAt->copy()->addMinutes(110),
                'note' => 'Response operations completed. Site secured and declared resolved.',
            ],
        ];

        $sequence = match ($targetStatus) {
            'Reported' => ['Reported'],
            'Received' => ['Reported', 'Received'],
            'Dispatched' => ['Reported', 'Received', 'Dispatched'],
            'Completed' => ['Reported', 'Received', 'Dispatched', 'Completed'],
            default => ['Reported'],
        };

        // Wipe old histories for idempotent re-seeding
        IncidentHistory::query()->where('incident_id', $incident->id)->delete();

        foreach ($sequence as $step) {
            $stage = $historyStages[$step];
            $statusId = DB::table('incident_statuses')->where('name', $stage['status'])->value('id');

            IncidentHistory::query()->create([
                'incident_id' => $incident->id,
                'status_id' => $statusId,
                'changed_by' => $stage['user_id'],
                'notes' => $stage['note'],
                'changed_at' => $stage['time'],
            ]);
        }
    }
}
