<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\UpdateIncidentStatusRequest;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $incidents = Incident::query()
            ->with('reporter')
            ->when(request('category'), fn ($query, $category) => $query->where('category', $category))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('barangay'), fn ($query, $barangay) => $query->where('barangay', 'like', '%'.$barangay.'%'))
            ->orderByRaw("FIELD(severity, 'Critical', 'High', 'Moderate', 'Low')")
            ->orderByRaw("FIELD(status, 'Reported', 'Received', 'Dispatched', 'Completed')")
            ->orderByDesc('reported_at')
            ->paginate(15)
            ->withQueryString();

        if (request()->ajax()) {
            return view('dispatcher.incidents.partials.results', compact('incidents'));
        }

        return view('dispatcher.incidents.index', [
            'incidents' => $incidents,
            'categories' => Incident::query()->distinct()->orderBy('category')->pluck('category'),
            'statuses' => ['Reported', 'Received', 'Dispatched', 'Completed'],
        ]);
    }

    public function show(Incident $incident): View
    {
        $incident->load(['reporter', 'history.dispatcher', 'history.status']);

        return view('dispatcher.incidents.show', compact('incident'));
    }

    public function updateStatus(
        UpdateIncidentStatusRequest $request,
        Incident $incident,
    ): RedirectResponse {
        $data = $request->validated();
        $status = $data['status'];

        abort_unless($this->isValidTransition($incident->status, $status), 422, 'Invalid incident status transition.');

        DB::transaction(function () use ($incident, $data, $request, $status): void {
            $statusId = DB::table('incident_statuses')->where('name', $status)->value('id');
            $incident->update([
                'status' => $status,
                'status_id' => $statusId,
                'dispatched_at' => $status === 'Dispatched' ? now() : $incident->dispatched_at,
                'completed_at' => $status === 'Completed' ? now() : $incident->completed_at,
            ]);

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
        $incidents = Incident::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'category', 'status', 'barangay', 'latitude', 'longitude', 'impact_radius', 'severity'])
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
