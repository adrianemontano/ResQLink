<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\CancelDispatchRequest;
use App\Http\Requests\Dispatcher\DispatchTeamRequest;
use App\Models\Incident;
use App\Models\IncidentDispatch;
use App\Services\IncidentDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Sends additional teams to a Dispatched incident and cancels dispatches.
 * The first team is chosen when the incident moves from Received to Dispatched.
 */
class IncidentDispatchController extends Controller
{
    public function __construct(private readonly IncidentDispatchService $dispatches)
    {
    }

    public function store(DispatchTeamRequest $request, Incident $incident): RedirectResponse
    {
        $data = $request->validated();

        $dispatched = DB::transaction(function () use ($incident, $data, $request): bool {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status !== 'Dispatched') {
                return false;
            }

            $this->dispatches->dispatchTeam(
                $lockedIncident, (int) $data['team_id'], $request->user(), $data['notes'] ?? null, now(), recordHistory: true,
            );

            return true;
        });

        if (! $dispatched) {
            return redirect()->route('dispatcher.incidents.show', $incident)
                ->withErrors(['team_id' => 'Another team can only be sent while the incident is Dispatched.']);
        }

        return redirect()->route('dispatcher.incidents.show', $incident)
            ->with('status', 'Another team was dispatched.');
    }

    public function cancel(CancelDispatchRequest $request, Incident $incident, IncidentDispatch $dispatch): RedirectResponse
    {
        abort_unless($dispatch->incident_id === $incident->id, 404);

        $returnedToReceived = DB::transaction(function () use ($incident, $dispatch, $request): ?bool {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            $lockedDispatch = IncidentDispatch::query()->lockForUpdate()->findOrFail($dispatch->id);
            if (! $lockedDispatch->isActive() || $lockedIncident->status === 'Completed') {
                return null;
            }

            return $this->dispatches->cancel(
                $lockedIncident, $lockedDispatch, $request->user(), $request->validated('cancellation_reason'),
            );
        });

        if ($returnedToReceived === null) {
            return redirect()->route('dispatcher.incidents.show', $incident)
                ->withErrors(['cancellation_reason' => 'This dispatch can no longer be cancelled.']);
        }

        $message = $returnedToReceived
            ? 'Dispatch cancelled. No team is assigned now, so the incident is back to Received.'
            : 'Dispatch cancelled.';

        return redirect()->route('dispatcher.incidents.show', $incident)->with('status', $message);
    }
}
