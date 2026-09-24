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
            ->orderByRaw("CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Moderate' THEN 3 WHEN 'Low' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE status WHEN 'Reported' THEN 1 WHEN 'Received' THEN 2 WHEN 'Dispatched' THEN 3 WHEN 'Completed' THEN 4 ELSE 5 END")
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
        return view('dispatcher.map');
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
