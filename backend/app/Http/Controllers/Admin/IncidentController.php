<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.incidents.index', ['incidents' => $incidents]);
    }
}
