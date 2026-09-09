<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Incident::query()
            ->with('reporter')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search): void {
                    $query->where('id', $search)
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('barangay', 'like', "%{$search}%")
                        ->orWhere('landmark', 'like', "%{$search}%")
                        ->orWhereHas('reporter', fn ($reporter) => $reporter->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('reported_at');

        return view('admin.incidents.index', [
            'incidents' => $query->paginate(15)->withQueryString(),
            'categories' => Incident::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => Incident::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function show(Incident $incident): View
    {
        return view('admin.incidents.show', [
            'incident' => $incident->load(['reporter', 'history.dispatcher', 'history.status']),
        ]);
    }
}
