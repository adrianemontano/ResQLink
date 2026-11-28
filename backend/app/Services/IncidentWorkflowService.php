<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\ResponseTeam;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class IncidentWorkflowService
{
    private const ALLOWED_TRANSITIONS = [
        'Reported' => ['Received'],
        'Received' => ['Dispatched'],
        'Dispatched' => ['Received', 'Dispatched', 'Completed'],
        'Completed' => [],
    ];

    public function updateStatus(Incident $incident, array $data, User $dispatcher): array
    {
        $status = $data['status'];
        $changedAt = CarbonImmutable::parse($data['changed_at'], 'Asia/Manila')
            ->setTimezone(config('app.timezone', 'UTC'));
        $teamId = ! empty($data['response_team_id']) ? (int) $data['response_team_id'] : null;
        $externalTeamName = $data['external_team_name'] ?? null;
        $severity = $data['severity'] ?? null;
        $cancelTeamIds = $data['cancel_team_ids'] ?? [];
        $dispatchTeamIds = $data['dispatch_team_ids'] ?? [];

        return DB::transaction(function () use (
            $incident,
            $data,
            $dispatcher,
            $status,
            $changedAt,
            $teamId,
            $externalTeamName,
            $severity,
            $cancelTeamIds,
            $dispatchTeamIds,
        ): array {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incidents can no longer be edited or changed.',
                ]);
            }
            if (! $this->isValidTransition($lockedIncident->status, $status)) {
                throw ValidationException::withMessages([
                    'status' => $this->invalidTransitionMessage($lockedIncident->status),
                ]);
            }

            $statusId = DB::table('incident_statuses')->where('name', $status)->value('id');
            $severityId = $severity
                ? DB::table('severity_levels')->where('name', $severity)->value('id')
                : $lockedIncident->severity_id;
            $isCancel = $status === 'Received' && $lockedIncident->status === 'Dispatched';

            if ($isCancel && ! empty($cancelTeamIds)) {
                foreach ($cancelTeamIds as $cancelTeamId) {
                    $lockedIncident->responseTeams()
                        ->wherePivot('response_team_id', $cancelTeamId)
                        ->wherePivot('status', 'active')
                        ->updateExistingPivot($cancelTeamId, [
                            'cancelled_at' => $changedAt,
                            'cancelled_by' => $dispatcher->id,
                            'cancel_reason' => $data['notes'] ?? null,
                            'status' => 'cancelled',
                        ]);
                }
            }

            $dispatchedTeamIds = [];
            if ($status === 'Dispatched') {
                if (! empty($dispatchTeamIds)) {
                    foreach ($dispatchTeamIds as $dispatchTeamId) {
                        $lockedIncident->responseTeams()->syncWithoutDetaching([
                            $dispatchTeamId => [
                                'dispatched_at' => $changedAt,
                                'dispatched_by' => $dispatcher->id,
                                'status' => 'active',
                            ],
                        ]);
                        $dispatchedTeamIds[] = $dispatchTeamId;
                    }
                } elseif ($teamId) {
                    $lockedIncident->responseTeams()->syncWithoutDetaching([
                        $teamId => [
                            'dispatched_at' => $changedAt,
                            'dispatched_by' => $dispatcher->id,
                            'status' => 'active',
                        ],
                    ]);
                    $dispatchedTeamIds[] = $teamId;
                }

                $externalTeamNames = $data['external_team_name'] ?? [];
                if (is_array($externalTeamNames)) {
                    foreach ($externalTeamNames as $extName) {
                        if (! empty($extName)) {
                            $externalTeam = ResponseTeam::firstOrCreate(
                                ['name' => $extName],
                                ['type' => 'External', 'is_active' => true],
                            );
                            $lockedIncident->responseTeams()->syncWithoutDetaching([
                                $externalTeam->id => [
                                    'dispatched_at' => $changedAt,
                                    'dispatched_by' => $dispatcher->id,
                                    'status' => 'active',
                                ],
                            ]);
                            $dispatchedTeamIds[] = $externalTeam->id;
                        }
                    }
                } elseif ($externalTeamName) {
                    $externalTeam = ResponseTeam::firstOrCreate(
                        ['name' => $externalTeamName],
                        ['type' => 'External', 'is_active' => true],
                    );
                    $lockedIncident->responseTeams()->syncWithoutDetaching([
                        $externalTeam->id => [
                            'dispatched_at' => $changedAt,
                            'dispatched_by' => $dispatcher->id,
                            'status' => 'active',
                        ],
                    ]);
                    $dispatchedTeamIds[] = $externalTeam->id;
                }

                if (! empty($dispatchedTeamIds)) {
                    $lockedIncident->dispatched_team_id = (int) $dispatchedTeamIds[0];
                }
                $lockedIncident->external_team_name = is_array($externalTeamName)
                    ? implode(', ', $externalTeamName)
                    : $externalTeamName;
            }

            $updates = [
                'status' => $status,
                'status_id' => $statusId,
                'received_at' => $status === 'Received' && ! $lockedIncident->received_at
                    ? $changedAt
                    : $lockedIncident->received_at,
                'dispatched_at' => $status === 'Dispatched' ? $changedAt : $lockedIncident->dispatched_at,
                'completed_at' => $status === 'Completed' ? $changedAt : $lockedIncident->completed_at,
            ];
            if ($severity) {
                $updates['severity'] = $severity;
                $updates['severity_id'] = $severityId;
            }
            $lockedIncident->update($updates);

            $historyTeamIds = $status === 'Dispatched'
                ? $dispatchedTeamIds
                : ($isCancel ? $cancelTeamIds : []);
            $historyData = [
                'status_id' => $statusId,
                'changed_by' => $dispatcher->id,
                'notes' => $data['notes'],
                'changed_at' => $changedAt,
            ];
            if (Schema::hasColumn('incident_histories', 'entry_type')) {
                $historyData['entry_type'] = IncidentHistory::ENTRY_TYPE_STATUS_CHANGE;
                if ($isCancel) {
                    $historyData['category'] = 'Resource cancelled';
                }
            }
            if (Schema::hasColumn('incident_histories', 'response_team_id')) {
                $historyResponseTeamId = $teamId ?? ($historyTeamIds[0] ?? null);
                if ($historyResponseTeamId !== null) {
                    $historyData['response_team_id'] = (int) $historyResponseTeamId;
                }
            }
            if (Schema::hasColumn('incident_histories', 'external_team_name') && $externalTeamName) {
                $historyData['external_team_name'] = is_array($externalTeamName)
                    ? implode(', ', $externalTeamName)
                    : $externalTeamName;
            }
            $lockedIncident->history()->create($historyData);

            $teamName = $historyTeamIds
                ? ResponseTeam::query()->whereIn('id', $historyTeamIds)->orderBy('name')->pluck('name')->implode(', ')
                : ($teamId
                    ? ResponseTeam::query()->whereKey($teamId)->value('name')
                    : (is_array($externalTeamName) ? implode(', ', $externalTeamName) : ($externalTeamName ?? null)));

            return [
                'dispatcher' => $dispatcher->name,
                'changed_at' => $changedAt->timezone('Asia/Manila')->format('M d, Y, h:i A'),
                'team_name' => $teamName,
                'severity' => $lockedIncident->severity,
                'category' => $isCancel ? 'Resource cancelled' : null,
            ];
        });
    }

    public function addUpdate(Incident $incident, array $data, User $dispatcher): array
    {
        $changedAt = CarbonImmutable::parse($data['changed_at'], 'Asia/Manila')
            ->setTimezone(config('app.timezone', 'UTC'));
        $teamId = ! empty($data['response_team_id']) ? (int) $data['response_team_id'] : null;

        return DB::transaction(function () use ($incident, $data, $dispatcher, $changedAt, $teamId): array {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incidents can no longer be edited or changed.',
                ]);
            }

            $statusId = DB::table('incident_statuses')->where('name', $lockedIncident->status)->value('id');
            $historyData = [
                'status_id' => $statusId,
                'changed_by' => $dispatcher->id,
                'notes' => $data['notes'],
                'changed_at' => $changedAt,
            ];
            if (Schema::hasColumn('incident_histories', 'entry_type')) {
                $historyData['entry_type'] = IncidentHistory::ENTRY_TYPE_UPDATE;
                $historyData['category'] = $data['category'];
            }
            if (Schema::hasColumn('incident_histories', 'response_team_id') && $teamId !== null) {
                $historyData['response_team_id'] = $teamId;
            }
            $lockedIncident->history()->create($historyData);

            return [
                'dispatcher' => $dispatcher->name,
                'changed_at' => $changedAt->timezone('Asia/Manila')->format('M d, Y, h:i A'),
                'status' => $lockedIncident->status,
                'team_name' => $teamId ? ResponseTeam::query()->whereKey($teamId)->value('name') : null,
            ];
        });
    }

    public function updateHistory(Incident $incident, IncidentHistory $history, string $notes): string
    {
        return DB::transaction(function () use ($incident, $history, $notes): string {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incident history can no longer be edited.',
                ]);
            }

            $lockedHistory = IncidentHistory::query()
                ->where('incident_id', $lockedIncident->id)
                ->lockForUpdate()
                ->findOrFail($history->id);
            $lockedHistory->notes = trim($notes);
            $lockedHistory->save();

            return $lockedHistory->notes;
        });
    }

    private function isValidTransition(?string $currentStatus, string $nextStatus): bool
    {
        return in_array($nextStatus, self::ALLOWED_TRANSITIONS[$currentStatus] ?? [], true);
    }

    private function invalidTransitionMessage(?string $currentStatus): string
    {
        $label = $currentStatus ?? 'This';
        $next = array_values(array_diff(self::ALLOWED_TRANSITIONS[$currentStatus] ?? [], [$currentStatus]));

        return $next === []
            ? "{$label} incidents can no longer change status."
            : "{$label} incidents can only move to " . implode(' or ', $next) . ' next.';
    }
}
