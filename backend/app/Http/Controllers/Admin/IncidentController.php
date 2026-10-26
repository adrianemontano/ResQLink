<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);
        $search = trim($filters['search'] ?? '');

        $query = Incident::query()
            ->with(['reporter', 'volunteer'])
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByDesc('reported_at')
            ->orderByDesc('id');

        return view('admin.incidents.index', [
            'incidents' => $query->paginate(15)->withQueryString(),
            'categories' => Incident::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => Incident::query()->whereNotNull('status')->distinct()->orderBy('status')->pluck('status'),
            'filters' => $filters,
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

    private function applySearch(Builder $query, string $search): void
    {
        $identifier = $this->incidentIdentifier($search);
        $searchTerm = "%{$search}%";
        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [$search];

        $query->where(function (Builder $query) use ($identifier, $searchTerm, $terms): void {
            if ($identifier !== null) {
                $query->whereKey($identifier);
            }

            foreach (['category', 'barangay', 'landmark', 'nearest_landmark', 'notes', 'status', 'severity'] as $column) {
                $query->orWhere($column, 'like', $searchTerm);
            }

            foreach (['affected_population', 'persons_count', 'latitude', 'longitude', 'impact_radius'] as $column) {
                $query->orWhereRaw("CAST({$column} AS CHAR) LIKE ?", [$searchTerm]);
            }

            $query->orWhereRaw(
                'CAST(COALESCE(incidents.reported_at, incidents.created_at) AS CHAR) LIKE ?',
                [$searchTerm],
            );

            foreach (['reporter', 'volunteer'] as $relationship) {
                $query->orWhereHas($relationship, function (Builder $reporter) use ($terms): void {
                    foreach ($terms as $term) {
                        $reporter->where(function (Builder $reporterFields) use ($term): void {
                            $pattern = "%{$term}%";
                            $reporterFields->where('name', 'like', $pattern)
                                ->orWhere('first_name', 'like', $pattern)
                                ->orWhere('last_name', 'like', $pattern)
                                ->orWhere('email', 'like', $pattern)
                                ->orWhere('username', 'like', $pattern);
                        });
                    }
                });
            }
        });
    }

    private function incidentIdentifier(string $search): ?int
    {
        if (ctype_digit($search)) {
            return (int) $search;
        }

        if (preg_match('/^INC-(\d+)$/i', $search, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
