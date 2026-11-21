<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Services\IncidentSubmissionService;
use Illuminate\Http\RedirectResponse;
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
        $user = Auth::user();
        $incidents = Incident::where('volunteer_id', $user->getKey())
            ->orWhere('reported_by', $user->getKey())
            ->latest('reported_at')
            ->get();

        return view('volunteer.incidents.index', compact('incidents'));
    }

    public function edit(Incident $incident): View
    {
        $this->verifyVolunteerOwnsIncident($incident);

        return view('volunteer.incidents.edit', compact('incident'));
    }

    public function update(
        UpdateIncidentRequest $request,
        Incident $incident,
    ): RedirectResponse {
        $this->verifyVolunteerOwnsIncident($incident);

        $data = $request->validated();

        $incident->update($data);

        return redirect()->route('volunteer.dashboard')
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
