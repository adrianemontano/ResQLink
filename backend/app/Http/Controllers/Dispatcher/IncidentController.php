<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\AddIncidentUpdateRequest;
use App\Http\Requests\Dispatcher\UpdateIncidentStatusRequest;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\ResponseTeam;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IncidentController extends Controller
{
    private const ALLOWED_TRANSITIONS = [
        'Reported' => ['Received'],
        'Received' => ['Dispatched'],
        'Dispatched' => ['Completed'],
        'Completed' => [],
    ];

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
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $status = $data['status'];
        $changedAt = CarbonImmutable::parse($data['changed_at'], 'Asia/Manila')
            ->setTimezone(config('app.timezone', 'UTC'));
        $teamId = ! empty($data['response_team_id']) ? (int) $data['response_team_id'] : null;
        $externalTeamName = $data['external_team_name'] ?? null;
        $severity = $data['severity'] ?? null;
        $cancelTeamIds = $data['cancel_team_ids'] ?? [];
        $dispatchTeamIds = $data['dispatch_team_ids'] ?? [];

        $change = DB::transaction(function () use ($incident, $data, $request, $status, $changedAt, $teamId, $externalTeamName, $severity, $cancelTeamIds, $dispatchTeamIds): array {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incidents can no longer be edited or changed.',
                ]);
            }

            $statusId = DB::table('incident_statuses')->where('name', $status)->value('id');
            $severityId = $severity
                ? DB::table('severity_levels')->where('name', $severity)->value('id')
                : $lockedIncident->severity_id;

            // Cancel-dispatch: going back to Received from Dispatched
            $isCancel = $status === 'Received' && $lockedIncident->status === 'Dispatched';

            // Handle team cancellations
            if ($isCancel && ! empty($cancelTeamIds)) {
                foreach ($cancelTeamIds as $cancelTeamId) {
                    $lockedIncident->responseTeams()
                        ->wherePivot('response_team_id', $cancelTeamId)
                        ->wherePivot('status', 'active')
                        ->updateExistingPivot($cancelTeamId, [
                            'cancelled_at' => $changedAt,
                            'cancelled_by' => $request->user()->id,
                            'cancel_reason' => $data['notes'] ?? null,
                            'status' => 'cancelled',
                        ]);
                }
            }

            $dispatchedTeamIds = [];

            // Handle team dispatch assignment
            if ($status === 'Dispatched') {
                if (! empty($dispatchTeamIds)) {
                    // Dispatch multiple teams via checkboxes
                    foreach ($dispatchTeamIds as $dispatchTeamId) {
                        $lockedIncident->responseTeams()->syncWithoutDetaching([
                            $dispatchTeamId => [
                                'dispatched_at' => $changedAt,
                                'dispatched_by' => $request->user()->id,
                                'status' => 'active',
                            ],
                        ]);
                        $dispatchedTeamIds[] = $dispatchTeamId;
                    }
                } elseif ($teamId) {
                    // Fallback to single team selection (legacy)
                    $lockedIncident->responseTeams()->syncWithoutDetaching([
                        $teamId => [
                            'dispatched_at' => $changedAt,
                            'dispatched_by' => $request->user()->id,
                            'status' => 'active',
                        ],
                    ]);
                    $dispatchedTeamIds[] = $teamId;
                }

                // Handle external teams
                $externalTeamNames = $data['external_team_name'] ?? [];
                if (is_array($externalTeamNames)) {
                    foreach ($externalTeamNames as $index => $extName) {
                        if (! empty($extName)) {
                            $externalTeam = ResponseTeam::firstOrCreate(
                                ['name' => $extName],
                                [
                                    'type' => 'External',
                                    'is_active' => true,
                                ]
                            );
                            $lockedIncident->responseTeams()->syncWithoutDetaching([
                                $externalTeam->id => [
                                    'dispatched_at' => $changedAt,
                                    'dispatched_by' => $request->user()->id,
                                    'status' => 'active',
                                ],
                            ]);
                            $dispatchedTeamIds[] = $externalTeam->id;
                        }
                    }
                } elseif ($externalTeamName) {
                    // Legacy single external team
                    $externalTeam = ResponseTeam::firstOrCreate(
                        ['name' => $externalTeamName],
                        [
                            'type' => 'External',
                            'is_active' => true,
                        ]
                    );
                    $lockedIncident->responseTeams()->syncWithoutDetaching([
                        $externalTeam->id => [
                            'dispatched_at' => $changedAt,
                            'dispatched_by' => $request->user()->id,
                            'status' => 'active',
                        ],
                    ]);
                    $dispatchedTeamIds[] = $externalTeam->id;
                }

                // Set the first team as the legacy single team field for backward compatibility
                if (! empty($dispatchedTeamIds)) {
                    $lockedIncident->dispatched_team_id = (int) $dispatchedTeamIds[0];
                }
                // Convert array to comma-separated string if multiple external teams
                $lockedIncident->external_team_name = is_array($externalTeamName) ? implode(', ', $externalTeamName) : $externalTeamName;
            }

            $updates = [
                'status'             => $status,
                'status_id'          => $statusId,
                'received_at'        => $status === 'Received' && ! $lockedIncident->received_at
                    ? $changedAt
                    : $lockedIncident->received_at,
                'dispatched_at'      => $status === 'Dispatched' ? $changedAt : $lockedIncident->dispatched_at,
                'completed_at'       => $status === 'Completed' ? $changedAt : $lockedIncident->completed_at,
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
                'status_id'  => $statusId,
                'changed_by' => $request->user()->id,
                'notes'      => $data['notes'],
                'changed_at' => $changedAt,
            ];
            if (Schema::hasColumn('incident_histories', 'entry_type')) {
                $historyData['entry_type'] = IncidentHistory::ENTRY_TYPE_STATUS_CHANGE;
                if ($isCancel) {
                    $historyData['category'] = 'Dispatch Cancelled';
                }
            }
            if (Schema::hasColumn('incident_histories', 'response_team_id')) {
                $historyResponseTeamId = $teamId ?? ($historyTeamIds[0] ?? null);
                if ($historyResponseTeamId !== null) {
                    $historyData['response_team_id'] = (int) $historyResponseTeamId;
                }
            }
            if (Schema::hasColumn('incident_histories', 'external_team_name') && $externalTeamName) {
                // Convert array to comma-separated string if multiple external teams
                $externalTeamNameStr = is_array($externalTeamName) ? implode(', ', $externalTeamName) : $externalTeamName;
                $historyData['external_team_name'] = $externalTeamNameStr;
            }
            $lockedIncident->history()->create($historyData);

            $teamName = $historyTeamIds
                ? ResponseTeam::query()->whereIn('id', $historyTeamIds)->orderBy('name')->pluck('name')->implode(', ')
                : ($teamId
                    ? ResponseTeam::query()->whereKey($teamId)->value('name')
                    : (is_array($externalTeamName) ? implode(', ', $externalTeamName) : ($externalTeamName ?? null)));

            return [
                'dispatcher' => $request->user()->name,
                'changed_at' => $changedAt->timezone('Asia/Manila')->format('M d, Y, h:i A'),
                'team_name' => $teamName,
                'severity' => $lockedIncident->severity,
            ];
        });

        $message = 'Incident status updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'incident_id' => $incident->id,
                'status' => $status,
                'severity' => $change['severity'],
                'team_name' => $change['team_name'],
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
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $changedAt = CarbonImmutable::parse($data['changed_at'], 'Asia/Manila')
            ->setTimezone(config('app.timezone', 'UTC'));
        $teamId = ! empty($data['response_team_id']) ? (int) $data['response_team_id'] : null;

        $result = DB::transaction(function () use ($incident, $data, $request, $changedAt, $teamId): array {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);
            if ($lockedIncident->status === 'Completed') {
                throw ValidationException::withMessages([
                    'status' => 'Completed incidents can no longer be edited or changed.',
                ]);
            }

            $statusId = DB::table('incident_statuses')->where('name', $lockedIncident->status)->value('id');

            $historyData = [
                'status_id' => $statusId,
                'changed_by' => $request->user()->id,
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

            $teamName = $teamId ? ResponseTeam::query()->whereKey($teamId)->value('name') : null;

            return [
                'dispatcher' => $request->user()->name,
                'changed_at' => $changedAt->timezone('Asia/Manila')->format('M d, Y, h:i A'),
                'status' => $lockedIncident->status,
                'team_name' => $teamName,
            ];
        });

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

    public function updateHistory(\Illuminate\Http\Request $request, Incident $incident, IncidentHistory $history): JsonResponse
    {
        abort_unless((int) $history->incident_id === (int) $incident->id, 404);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:300'],
        ]);

        $notes = DB::transaction(function () use ($incident, $history, $validated): string {
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
            $lockedHistory->notes = trim($validated['notes']);
            $lockedHistory->save();

            return $lockedHistory->notes;
        });

        return response()->json([
            'message' => 'Note updated successfully.',
            'notes' => $notes,
        ]);
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
            : "{$label} incidents can only move to {$next[0]} next.";
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
