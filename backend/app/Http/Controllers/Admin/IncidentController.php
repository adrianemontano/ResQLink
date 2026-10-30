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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:30'],
            'scope' => ['nullable', 'in:all,active,archived'],
        ]);

        $scope = in_array($filters['scope'] ?? null, self::RECORD_SCOPES, true)
            ? $filters['scope']
            : 'all';

        $query = AdminIncidentRecordQuery::apply(Incident::query(), $filters, $scope)
            ->orderByDesc('reported_at')
            ->orderByDesc('id');

        return view('admin.incidents.index', [
            'incidents' => $query->paginate(15)->withQueryString(),
            'categories' => Incident::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => Incident::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'filters' => [...$filters, 'scope' => $scope],
            'recordCounts' => AdminIncidentRecordQuery::countsByScope(),
        ]);
    }

    public function show(Incident $incident): View
    {
        return view('admin.incidents.show', [
            'incident' => $incident->load([
                'reporter',
                'volunteer',
                'history.dispatcher',
                'history.status',
            ]),
        ]);
    }
}
