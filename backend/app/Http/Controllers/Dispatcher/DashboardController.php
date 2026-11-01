<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ACTIVE_STATUSES = ['Reported', 'Received', 'Dispatched'];

    public function __invoke(): View
    {
        return view('dispatcher.dashboard', [
            'activeIncidents' => $this->countByStatuses(self::ACTIVE_STATUSES),
            'completedIncidents' => $this->countByStatuses(['Completed']),
            'incidents' => Incident::query()
                ->withReferenceLabels()
                ->with('reporter')
                ->whereIn('incident_statuses.name', self::ACTIVE_STATUSES)
                ->orderByRaw("CASE severity_levels.name WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Moderate' THEN 3 WHEN 'Low' THEN 4 ELSE 5 END")
                ->orderByRaw("CASE incident_statuses.name WHEN 'Reported' THEN 1 WHEN 'Received' THEN 2 WHEN 'Dispatched' THEN 3 ELSE 4 END")
                ->orderByDesc('reported_at')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Count incidents by their normalized workflow status.
     *
     * @param array<int, string> $statuses
     */
    private function countByStatuses(array $statuses): int
    {
        return Incident::query()
            ->join('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
            ->whereIn('incident_statuses.name', $statuses)
            ->count('incidents.id');
    }
}
