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
        $mediaFiles = $data['media'] ?? [];
        unset($data['photo'], $data['media']);

        $severity = $this->severityService->classify(
            (int) $data['affected_population'],
            (float) $data['impact_radius'],
        );

        $incident = DB::transaction(function () use ($data, $reporter, $severity, $photo, $mediaFiles): Incident {
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

            $uploads = [];
            if ($photo instanceof UploadedFile) {
                $uploads[] = $photo;
            }
            foreach (is_array($mediaFiles) ? $mediaFiles : [] as $file) {
                if ($file instanceof UploadedFile) {
                    $uploads[] = $file;
                }
            }

            foreach ($uploads as $file) {
                $mime = $file->getMimeType() ?: 'image/jpeg';
                $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('incident-photos', 'local');
                $incident->photos()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mime,
                    'media_type' => $mediaType,
                    'size' => $file->getSize(),
                ]);
            }

            return $incident;
        });

        // Notify all dispatchers and admins of the incoming incident
        $recipients = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['dispatcher', 'admin']))
            ->get();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewIncidentReportedNotification($incident));
        }

        return $incident;
    }
}
