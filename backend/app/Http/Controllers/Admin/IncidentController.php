<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Queries\AdminIncidentRecordQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Allowed read-only record scopes for the admin incident workspace.
     *
     * @var array<int, string>
     */
    private const RECORD_SCOPES = ['all', 'active', 'archived'];

    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'scope' => ['nullable', 'in:all,active,archived'],
        ]);

        $scope = in_array($request->query('scope'), self::RECORD_SCOPES, true)
            ? $request->query('scope')
            : 'all';

        $query = AdminIncidentRecordQuery::apply(
            Incident::query(),
            $request->only(['search', 'category', 'status']),
            $scope,
        )->latest('reported_at');

        return view('admin.incidents.index', [
            'incidents' => $query->paginate(15)->withQueryString(),
            'categories' => Incident::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => Incident::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'filters' => [...$request->only(['search', 'category', 'status']), 'scope' => $scope],
            'recordCounts' => AdminIncidentRecordQuery::countsByScope(),
        ]);
    }

    public function show(Incident $incident): View
    {
        return view('admin.incidents.show', [
            'incident' => $incident->load(['reporter', 'history.dispatcher', 'history.status']),
        ]);
    }
}
