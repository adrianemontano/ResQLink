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
                ->orderByRaw("FIELD(severity, 'Critical', 'High', 'Moderate', 'Low')")
                ->orderByRaw("FIELD(status, 'Reported', 'Received', 'Dispatched')")
                ->orderByDesc('reported_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
