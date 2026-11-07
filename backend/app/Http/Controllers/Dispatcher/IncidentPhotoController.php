<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentPhoto;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncidentPhotoController extends Controller
{
    /**
     * Securely stream an incident photo to authenticated dispatchers and admins.
     */
    public function show(Incident $incident, IncidentPhoto $photo): StreamedResponse|Response
    {
        if ($photo->incident_id !== $incident->id) {
            abort(404);
        }
        abort_unless(auth()->user()?->hasRole(['dispatcher', 'admin']), 403);
        if (! Storage::disk('local')->exists($photo->file_path)) {
            abort(404, 'Photo file not found on disk.');
        }

        if (! Storage::disk('local')->exists($photo->file_path)) {
            abort(404, 'Photo file not found on disk.');
        }

        return Storage::disk('local')->response(
            $photo->file_path,
            $photo->original_name ?? 'incident-photo.jpg',
            ['Content-Type' => $photo->mime_type ?: 'image/jpeg'],
        );
    }

    /**
     * Download the incident photo.
     */
    public function download(Incident $incident, IncidentPhoto $photo): StreamedResponse
    {
        if ($photo->incident_id !== $incident->id) {
            abort(404);
        }
        abort_unless(auth()->user()?->hasRole(['dispatcher', 'admin']), 403);

        return Storage::disk('local')->download(
            $photo->file_path,
            $photo->original_name ?? "incident-{$incident->id}-photo.jpg",
        );
    }
}
