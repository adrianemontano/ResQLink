<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IncidentSubmissionService
{
    public function __construct(private IncidentSeverityService $severityService) {}

    public function submit(array $data, User $reporter): Incident
    {
        $severity = $this->severityService->classify(
            (int) $data['affected_population'],
            (float) $data['impact_radius'],
        );

        return DB::transaction(function () use ($data, $reporter, $severity): Incident {
            $categoryId = DB::table('incident_categories')->where('name', $data['category'])->value('id');
            $severityId = DB::table('severity_levels')->where('name', $severity)->value('id');
            $statusId = DB::table('incident_statuses')->where('name', 'Reported')->value('id');

            return Incident::create([
                ...$data,
                'volunteer_id' => $reporter->id,
                'reported_by' => $reporter->id,
                'category_id' => $categoryId,
                'severity_id' => $severityId,
                'status_id' => $statusId,
                'category' => $data['category'],
                'nearest_landmark' => $data['nearest_landmark'],
                'landmark' => $data['nearest_landmark'],
                'persons_count' => $data['affected_population'],
                'severity' => $severity,
                'status' => 'Reported',
                'reported_at' => now(),
            ]);
        });
    }
}
