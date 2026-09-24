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
            'activeIncidents' => Incident::query()->whereIn('status', ['Reported', 'Received', 'Dispatched'])->count(),
            'completedIncidents' => Incident::query()->where('status', 'Completed')->count(),
            'incidents' => Incident::query()
                ->with('reporter')
                ->whereIn('status', ['Reported', 'Received', 'Dispatched'])
                ->orderByRaw("CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Moderate' THEN 3 WHEN 'Low' THEN 4 ELSE 5 END")
                ->orderByRaw("CASE status WHEN 'Reported' THEN 1 WHEN 'Received' THEN 2 WHEN 'Dispatched' THEN 3 ELSE 4 END")
                ->orderByDesc('reported_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
