<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\User;
use App\Notifications\NewIncidentReportedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class IncidentSubmissionService
{
    public function __construct(private IncidentSeverityService $severityService) {}

    public function submit(array $data, User $reporter): Incident
    {
        $photo = $data['photo'] ?? null;
        unset($data['photo']);

        $severity = $this->severityService->classify(
            (int) $data['affected_population'],
            (float) $data['impact_radius'],
        );

        $incident = DB::transaction(function () use ($data, $reporter, $severity, $photo): Incident {
            $categoryId = DB::table('incident_categories')->where('name', $data['category'])->value('id');
            $severityId = DB::table('severity_levels')->where('name', $severity)->value('id');
            $statusId = DB::table('incident_statuses')->where('name', 'Reported')->value('id');

            $incident = Incident::create([
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

            IncidentHistory::create([
                'incident_id' => $incident->id,
                'status_id' => $statusId,
                'changed_by' => $reporter->id,
                'notes' => 'Initial field emergency report submitted by Barangay Responder.',
                'changed_at' => now(),
            ]);

            if ($photo instanceof UploadedFile) {
                $path = $photo->store('incident-photos', 'local');
                $incident->photos()->create([
                    'file_path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType() ?: 'image/jpeg',
                    'size' => $photo->getSize(),
                ]);
            }

            return $incident;
        });

        // Notify all dispatchers of the incoming incident
        $dispatchers = User::query()->where('is_active', true)->whereRelation('role', 'name', 'dispatcher')->get();
        if ($dispatchers->isNotEmpty()) {
            Notification::send($dispatchers, new NewIncidentReportedNotification($incident));
        }

        return $incident;
    }
}
