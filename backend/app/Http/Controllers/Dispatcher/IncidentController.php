<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\UpdateIncidentStatusRequest;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $incidents = Incident::query()->withReferenceLabels()
            ->with('reporter')
            ->when(request('category'), fn ($query, $category) => $query->where('incident_categories.name', $category))
            ->when(request('status'), fn ($query, $status) => $query->where('incident_statuses.name', $status))
            ->when(request('barangay'), fn ($query, $barangay) => $query->where('barangays.name', 'like', '%'.$barangay.'%'))
            ->orderByRaw("FIELD(severity_levels.name, 'Critical', 'High', 'Moderate', 'Low')")
            ->orderByRaw("FIELD(incident_statuses.name, 'Reported', 'Received', 'Dispatched', 'Completed')")
            ->orderByDesc('reported_at')
            ->paginate(15)
            ->withQueryString();

        if (request()->ajax()) {
            return view('dispatcher.incidents.partials.results', compact('incidents'));
        }

        return view('dispatcher.incidents.index', [
            'incidents' => $incidents,
            'categories' => DB::table('incident_categories')->orderBy('name')->pluck('name'),
            'statuses' => ['Reported', 'Received', 'Dispatched', 'Completed'],
        ]);
    }

    public function show(Incident $incident): View
    {
        $incident = Incident::query()->withReferenceLabels()->findOrFail($incident->id);
        $incident->load(['reporter', 'history.dispatcher', 'history.status']);

        return view('dispatcher.incidents.show', compact('incident'));
    }

    public function updateStatus(
        UpdateIncidentStatusRequest $request,
        Incident $incident,
    ): RedirectResponse {
        $data = $request->validated();
        $status = $data['status'];

        $currentStatus = DB::table('incident_statuses')->where('id', $incident->status_id)->value('name');
        abort_unless($this->isValidTransition($currentStatus, $status), 422, 'Invalid incident status transition.');

        DB::transaction(function () use ($incident, $data, $request, $status): void {
            $statusId = DB::table('incident_statuses')->where('name', $status)->value('id');
            $updates = ['status_id' => $statusId];

            if (Schema::hasColumn('incidents', 'status')) {
                $updates['status'] = $status;
            }
            if (Schema::hasColumn('incidents', 'dispatched_at') && $status === 'Dispatched') {
                $updates['dispatched_at'] = now();
            }
            if (Schema::hasColumn('incidents', 'completed_at') && $status === 'Completed') {
                $updates['completed_at'] = now();
            }

            $incident->update($updates);

            $incident->history()->create([
                'status_id' => $statusId,
                'changed_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
                'changed_at' => now(),
            ]);
        });

        return redirect()->route('dispatcher.incidents.show', $incident)
            ->with('status', 'Incident status updated successfully.');
    }

    public function map(): View
    {
        $incidents = Incident::query()->withReferenceLabels()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['incidents.id', 'incidents.latitude', 'incidents.longitude', 'incidents.impact_radius', 'incident_categories.name as category', 'barangays.name as barangay', 'severity_levels.name as severity', 'incident_statuses.name as status'])
            ->each(function (Incident $incident): void {
                $incident->detail_url = route('dispatcher.incidents.show', $incident);
            });

        return view('dispatcher.map', ['incidents' => $incidents]);
    }

    private function isValidTransition(?string $currentStatus, string $nextStatus): bool
    {
        $allowed = [
            'Reported' => ['Reported', 'Received'],
            'Received' => ['Received', 'Dispatched'],
            'Dispatched' => ['Dispatched', 'Completed'],
            'Completed' => ['Completed'],
        ];

        return in_array($nextStatus, $allowed[$currentStatus] ?? [], true);
    }
}
