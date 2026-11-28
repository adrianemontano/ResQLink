<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\AddIncidentUpdateRequest;
use App\Http\Requests\Dispatcher\UpdateIncidentStatusRequest;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\ResponseTeam;
use App\Models\User;
use App\Services\IncidentWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $requestedPerPage = request()->query('per_page');
        $perPage = in_array($requestedPerPage, ['10', '25', '50'], true)
            ? (int) $requestedPerPage
            : 10;
        $baseQuery = Incident::query()
            ->when(request('category'), fn ($query, $category) => $query->where('category', $category))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('barangay'), fn ($query, $barangay) => $query->where('barangay', $barangay));

        $incidents = (clone $baseQuery)
            ->with(['reporter.volunteerProfile', 'photos'])
            ->orderByRaw('COALESCE(reported_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        if (request()->ajax()) {
            return view('dispatcher.incidents.partials.results', compact('incidents', 'perPage'));
        }

        return view('dispatcher.incidents.index', [
            'incidents' => $incidents,
            'perPage' => $perPage,
            'categories' => (clone $baseQuery)->select('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => ['Reported', 'Received', 'Dispatched', 'Completed'],
            'barangays' => request()->hasAny(['category', 'status', 'barangay'])
                ? (clone $baseQuery)->select('barangay')->distinct()->orderBy('barangay')->pluck('barangay')->filter()->values()->all()
                : $this->mapBarangays(),
        ]);
    }

    public function show(Incident $incident): View
    {
        $incident->load([
            'reporter.volunteerProfile',
            'dispatchedTeam',
            'responseTeams',
            'photos',
            'history.dispatcher',
            'history.status',
            'history.responseTeam',
        ]);

        // Mark any unread notifications for this incident as read.
        $this->markIncidentNotificationsRead(request()->user(), $incident->id);

        $responseTeams = ResponseTeam::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dispatcher.incidents.show', compact('incident', 'responseTeams'));
    }

    private function markIncidentNotificationsRead($user, int $incidentId): void
    {
        if (! $user) {
            return;
        }

        $user->unreadNotifications()
            ->whereJsonContains('data->incident_id', $incidentId)
            ->update(['read_at' => now()]);
    }

    public function updateStatus(
        UpdateIncidentStatusRequest $request,
        Incident $incident,
        IncidentWorkflowService $workflow,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $dispatcher = $request->user();
        abort_unless($dispatcher instanceof User, 403);

        $change = $workflow->updateStatus($incident, $data, $dispatcher);
        $message = 'Incident status updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'incident_id' => $incident->id,
                'status' => $data['status'],
                'severity' => $change['severity'],
                'team_name' => $change['team_name'],
                'category' => $change['category'],
                'entry_type' => IncidentHistory::ENTRY_TYPE_STATUS_CHANGE,
                'dispatcher' => $change['dispatcher'],
                'changed_at' => $change['changed_at'],
                'notes' => $data['notes'],
            ]);
        }

        return redirect()->route('dispatcher.incidents.show', $incident)
            ->with('incident_status_success', $message);
    }

    public function addUpdate(
        AddIncidentUpdateRequest $request,
        Incident $incident,
        IncidentWorkflowService $workflow,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $dispatcher = $request->user();
        abort_unless($dispatcher instanceof User, 403);

        $result = $workflow->addUpdate($incident, $data, $dispatcher);
        $message = 'Update added successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'incident_id' => $incident->id,
                'entry_type' => IncidentHistory::ENTRY_TYPE_UPDATE,
                'category' => $data['category'],
                'status' => $result['status'],
                'team_name' => $result['team_name'],
                'dispatcher' => $result['dispatcher'],
                'changed_at' => $result['changed_at'],
                'notes' => $data['notes'],
            ]);
        }

        return redirect()->route('dispatcher.incidents.show', $incident)
            ->with('incident_update_success', $message);
    }

    public function updateHistory(
        Request $request,
        Incident $incident,
        IncidentHistory $history,
        IncidentWorkflowService $workflow,
    ): JsonResponse {
        abort_unless((int) $history->incident_id === (int) $incident->id, 404);
        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:300'],
        ]);

        $notes = $workflow->updateHistory($incident, $history, $validated['notes']);

        return response()->json([
            'message' => 'Note updated successfully.',
            'notes' => $notes,
        ]);
    }

    private function mapBarangays(): array
    {
        return Incident::query()
            ->select('barangay')
            ->distinct()
            ->orderBy('barangay')
            ->pluck('barangay')
            ->filter()
            ->values()
            ->all();
    }
}
