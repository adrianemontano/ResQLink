<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\ResponseTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IncidentDispatchController extends Controller
{
    public function dispatchPage(Incident $incident): View
    {
        abort_if($incident->status === 'Completed', 403, 'Completed incidents can no longer be edited.');
        $incident->load(['responseTeams']);

        return view('dispatcher.incidents.dispatch', compact('incident'));
    }

    public function cancelDispatchPage(Incident $incident): View
    {
        abort_if($incident->status === 'Completed', 403, 'Completed incidents can no longer be edited.');
        $incident->load(['responseTeams']);

        return view('dispatcher.incidents.cancel-dispatch', compact('incident'));
    }

    public function fetchResponseTeams(Request $request): JsonResponse
    {
        $incidentId = $request->query('incident_id');
        $search = $request->query('q');
        $type = $request->query('type');
        $availableOnly = $request->query('available', '1') === '1';

        $query = ResponseTeam::query()
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name');

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $teams = $query->get();

        $busyTeamIds = Incident::query()
            ->where('status', 'Dispatched')
            ->when($incidentId, fn ($q) => $q->where('id', '!=', $incidentId))
            ->whereHas('responseTeams', fn ($q) => $q->where('status', 'active'))
            ->with('responseTeams')
            ->get()
            ->pluck('response_teams')
            ->flatten()
            ->pluck('id')
            ->toArray();

        $teams = $teams->map(function ($team) use ($busyTeamIds, $incidentId) {
            $isBusy = in_array($team->id, $busyTeamIds);
            $busyIncidentId = null;

            if ($isBusy) {
                $busyIncident = Incident::query()
                    ->where('status', 'Dispatched')
                    ->whereHas('responseTeams', fn ($q) => $q->where('response_team_id', $team->id)->where('status', 'active'))
                    ->when($incidentId, fn ($q) => $q->where('id', '!=', $incidentId))
                    ->first();
                $busyIncidentId = $busyIncident?->id;
            }

            return [
                'id' => $team->id,
                'name' => $team->name,
                'type' => $team->type,
                'barangay' => $team->barangay,
                'latitude' => $team->latitude,
                'longitude' => $team->longitude,
                'is_busy' => $isBusy,
                'busy_incident_id' => $busyIncidentId,
            ];
        });

        if ($availableOnly) {
            $teams = $teams->reject(fn ($team) => $team['is_busy'])->values();
        }

        return response()->json($teams);
    }

    public function cancelDispatch(Request $request, Incident $incident): JsonResponse|RedirectResponse
    {
        $teamIds = $request->input('team_ids', []);
        $reason = $request->input('cancel_reason') ?: $request->input('notes');

        if (empty($teamIds)) {
            if (! $request->wantsJson()) {
                return back()->withErrors(['team_ids' => 'No teams selected for cancellation.']);
            }

            return response()->json(['message' => 'No teams selected for cancellation.'], 422);
        }

        if (empty($reason)) {
            if (! $request->wantsJson()) {
                return back()->withErrors(['notes' => 'A cancellation reason is required.']);
            }

            return response()->json(['message' => 'A cancellation reason is required.'], 422);
        }

        if ($incident->status === 'Completed') {
            if (! $request->wantsJson()) {
                return back()->withErrors(['status' => 'Cannot cancel dispatch on a completed incident.']);
            }

            return response()->json(['message' => 'Cannot cancel dispatch on a completed incident.'], 422);
        }

        $cancelledCount = DB::transaction(function () use ($incident, $teamIds, $request, $reason) {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incidents can no longer be edited or dispatched.',
                ]);
            }
            $cancelledAt = now();

            $cancelledCount = 0;
            foreach ($teamIds as $teamId) {
                $updated = $lockedIncident->responseTeams()
                    ->wherePivot('response_team_id', $teamId)
                    ->wherePivot('status', 'active')
                    ->updateExistingPivot($teamId, [
                        'cancelled_at' => $cancelledAt,
                        'cancelled_by' => $request->user()->id,
                        'cancel_reason' => $reason,
                        'status' => 'cancelled',
                    ]);
                if ($updated) {
                    $cancelledCount++;
                }
            }

            $teamNames = ResponseTeam::whereIn('id', $teamIds)->pluck('name')->implode(', ');
            $historyData = [
                'status_id' => $lockedIncident->status_id,
                'changed_by' => $request->user()->id,
                'notes' => "Cancelled dispatch for: {$teamNames}. Reason: {$reason}",
                'changed_at' => $cancelledAt,
            ];
            if (Schema::hasColumn('incident_histories', 'entry_type')) {
                $historyData['entry_type'] = IncidentHistory::ENTRY_TYPE_STATUS_CHANGE;
                $historyData['category'] = 'Resource cancelled';
            }
            $lockedIncident->history()->create($historyData);

            return $cancelledCount;
        });

        if (! $request->wantsJson()) {
            return redirect()
                ->route('dispatcher.incidents.show', $incident)
                ->with('incident_status_success', "Cancelled dispatch for {$cancelledCount} team(s).");
        }

        return response()->json([
            'message' => "Cancelled dispatch for {$cancelledCount} team(s).",
            'cancelled_count' => $cancelledCount,
        ]);
    }
}
