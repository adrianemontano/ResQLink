<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Services\IncidentSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function create(): View
    {
        return view('volunteer.incidents.create');
    }

    public function store(
        StoreIncidentRequest $request,
        IncidentSubmissionService $submissionService,
    ): RedirectResponse {
        $incident = $submissionService->submit($request->validated(), $request->user());

        return redirect()->route('volunteer.incidents.create')
            ->with('incident', [
                'id' => $incident->getKey(),
                'status' => $incident->status,
                'severity' => $incident->severity,
            ]);
    }

    public function index(): View
    {
        $requestedPerPage = request()->query('per_page');
        $perPage = in_array($requestedPerPage, ['10', '25', '50'], true)
            ? (int) $requestedPerPage
            : 10;
        $user = Auth::user();
        abort_unless($user?->hasRole('volunteer'), 403);
        $incidents = Incident::query()
            ->where(function ($query) use ($user): void {
                $query->where('volunteer_id', $user->getKey())
                    ->orWhere('reported_by', $user->getKey());
            })
            ->latest('reported_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('volunteer.incidents.index', compact('incidents', 'perPage'));
    }

    public function edit(Incident $incident): View
    {
        $this->verifyVolunteerOwnsIncident($incident);
        $incident->load('photos');

        return view('volunteer.incidents.edit', compact('incident'));
    }

    public function update(
        UpdateIncidentRequest $request,
        Incident $incident,
    ): RedirectResponse {
        $this->verifyVolunteerOwnsIncident($incident);

        $data = $request->validated();
        $mediaFiles = $data['media'] ?? [];
        unset($data['media']);

        $incident->update($data);

        foreach (is_array($mediaFiles) ? $mediaFiles : [] as $file) {
            if ($file instanceof UploadedFile) {
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
        }

        return redirect()->route('volunteer.incidents.index')
            ->with('status', 'Incident report updated successfully.');
    }

    protected function verifyVolunteerOwnsIncident(Incident $incident): void
    {
        $user = Auth::user();

        if (! $user?->hasRole('volunteer')
            || $incident->volunteer_id !== $user->id
        ) {
            abort(403, 'Unauthorized action.');
        }
    }
}
