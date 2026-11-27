<?php

namespace App\Services;

use App\Models\IncidentDispatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * How often each team has been dispatched, for the Admin dashboard. Counts
 * uncancelled dispatches; a cancelled dispatch did not send the team out.
 */
class TeamDispatchFrequencyService
{
    public const WINDOW_DAYS = 30;

    /**
     * Teams ordered by how often they were dispatched in the last WINDOW_DAYS days.
     *
     * @return Collection<int, array{team: string, team_lead: string, is_active: bool, recent: int, per_week: float, total: int, last_dispatched_at: CarbonImmutable|null, on_call: bool}>
     */
    public function summary(): Collection
    {
        $windowStart = CarbonImmutable::now()->subDays(self::WINDOW_DAYS);
        $dispatches = IncidentDispatch::query()
            ->with('incident:id,status')
            ->whereNull('cancelled_at')
            ->get(['id', 'team_id', 'incident_id', 'dispatched_at'])
            ->groupBy('team_id');

        return Team::query()
            ->with('teamLead')
            ->orderBy('name')
            ->get()
            ->map(function (Team $team) use ($dispatches, $windowStart): array {
                $teamDispatches = $dispatches->get($team->id, collect());
                $recent = $teamDispatches->filter(fn (IncidentDispatch $dispatch) => $dispatch->dispatched_at->gte($windowStart))->count();
                $lastDispatchedAt = $teamDispatches->max('dispatched_at');

                return [
                    'team' => $team->name,
                    'team_lead' => $team->teamLead?->name ?? 'Not assigned',
                    'is_active' => $team->is_active,
                    'recent' => $recent,
                    'per_week' => round($recent / (self::WINDOW_DAYS / 7), 1),
                    'total' => $teamDispatches->count(),
                    'last_dispatched_at' => $lastDispatchedAt ? CarbonImmutable::parse($lastDispatchedAt) : null,
                    // Out on an incident that is not completed yet.
                    'on_call' => $teamDispatches->contains(
                        fn (IncidentDispatch $dispatch) => $dispatch->incident && $dispatch->incident->status !== 'Completed',
                    ),
                ];
            })
            ->sortByDesc('recent')
            ->values();
    }
}
