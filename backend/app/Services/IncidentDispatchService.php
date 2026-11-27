<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentDispatch;
use App\Models\IncidentHistory;
use App\Models\Team;
use App\Models\User;
use App\Rules\AvailableTeam;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Sends teams to incidents and cancels dispatches, recording each step in the
 * incident's history. Callers run these inside a transaction holding a lock on
 * the incident row.
 */
class IncidentDispatchService
{
    /**
     * Record a team dispatch. Used for the first dispatch (with the move to
     * Dispatched, whose status-change entry already documents it) and for
     * additional teams sent later, which get their own history entry.
     */
    public function dispatchTeam(
        Incident $incident,
        int $teamId,
        User $dispatcher,
        ?string $notes,
        Carbon $dispatchedAt,
        bool $recordHistory,
    ): IncidentDispatch {
        // Re-check under a row lock so two dispatchers cannot send the same
        // team at the same moment; validation alone cannot prevent that.
        $teamIsAvailable = Team::query()->availableForDispatch()->whereKey($teamId)->lockForUpdate()->exists();
        if (! $teamIsAvailable) {
            throw ValidationException::withMessages(['team_id' => AvailableTeam::MESSAGE]);
        }

        $dispatch = $incident->dispatches()->create([
            'team_id' => $teamId,
            'dispatched_by' => $dispatcher->id,
            'dispatched_at' => $dispatchedAt,
            'notes' => $notes,
        ]);

        if ($recordHistory) {
            $teamName = Team::query()->whereKey($teamId)->value('name');
            $this->recordUpdate($incident, $dispatcher, $dispatchedAt, trim("Dispatched another team: {$teamName}. {$notes}"));
        }

        return $dispatch;
    }

    /**
     * Cancel one dispatch. When no active dispatch remains on a Dispatched
     * incident, the incident returns to Received so a team can be sent again.
     *
     * @return bool Whether the incident returned to Received.
     */
    public function cancel(Incident $incident, IncidentDispatch $dispatch, User $dispatcher, string $reason): bool
    {
        $cancelledAt = now();
        $dispatch->update([
            'cancelled_at' => $cancelledAt,
            'cancelled_by' => $dispatcher->id,
            'cancellation_reason' => $reason,
        ]);

        $teamName = $dispatch->team?->name ?? 'Team';
        $this->recordUpdate($incident, $dispatcher, $cancelledAt, "Cancelled dispatch of {$teamName}. Reason: {$reason}");

        $hasActiveDispatch = $incident->dispatches()->active()->exists();
        if ($hasActiveDispatch || $incident->status !== 'Dispatched') {
            return false;
        }

        $this->returnToReceived($incident, $dispatcher, $cancelledAt);

        return true;
    }

    private function returnToReceived(Incident $incident, User $dispatcher, Carbon $changedAt): void
    {
        $statusId = DB::table('incident_statuses')->where('name', 'Received')->value('id');
        $incident->update(['status' => 'Received', 'status_id' => $statusId, 'dispatched_at' => null]);

        $history = [
            'status_id' => $statusId,
            'changed_by' => $dispatcher->id,
            'notes' => 'All dispatched teams were cancelled. The incident is waiting for a new dispatch.',
            'changed_at' => $changedAt,
        ];
        if (Schema::hasColumn('incident_histories', 'entry_type')) {
            $history['entry_type'] = IncidentHistory::ENTRY_TYPE_STATUS_CHANGE;
        }
        $incident->history()->create($history);
    }

    private function recordUpdate(Incident $incident, User $dispatcher, Carbon $changedAt, string $notes): void
    {
        $history = [
            'status_id' => $incident->status_id ?? DB::table('incident_statuses')->where('name', $incident->status)->value('id'),
            'changed_by' => $dispatcher->id,
            'notes' => mb_substr($notes, 0, 300),
            'changed_at' => $changedAt,
        ];
        if (Schema::hasColumn('incident_histories', 'entry_type')) {
            $history['entry_type'] = IncidentHistory::ENTRY_TYPE_UPDATE;
            $history['category'] = 'Coordination';
        }
        $incident->history()->create($history);
    }
}
