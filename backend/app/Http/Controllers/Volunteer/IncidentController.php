<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Services\IncidentSubmissionService;
use Illuminate\Http\RedirectResponse;
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
            ->with('incident', $incident);
    }
}
