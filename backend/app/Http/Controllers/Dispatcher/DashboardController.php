<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dispatcher.dashboard', [
            'activeIncidents' => Incident::query()->withReferenceLabels()->whereIn('incident_statuses.name', ['Reported', 'Received', 'Dispatched'])->count(),
            'completedIncidents' => Incident::query()->withReferenceLabels()->where('incident_statuses.name', 'Completed')->count(),
            'incidents' => Incident::query()->withReferenceLabels()
                ->with('reporter')
                ->whereIn('incident_statuses.name', ['Reported', 'Received', 'Dispatched'])
                ->orderByRaw("FIELD(severity_levels.name, 'Critical', 'High', 'Moderate', 'Low')")
                ->orderByRaw("FIELD(incident_statuses.name, 'Reported', 'Received', 'Dispatched')")
                ->orderByDesc('reported_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
