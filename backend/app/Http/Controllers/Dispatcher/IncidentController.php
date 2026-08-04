<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $incidents = Incident::query()
            ->with('reporter')
            ->latest()
            ->paginate(15);

        return view('dispatcher.incidents.index', ['incidents' => $incidents]);
    }

    public function map(): View
    {
        $incidents = Incident::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'category', 'status', 'barangay', 'latitude', 'longitude']);

        return view('dispatcher.map', ['incidents' => $incidents]);
    }
}
