<?php

namespace App\Services;

use App\Models\IncidentDispatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Per-team dispatch totals for one Admin report period. Every team is listed,
 * including teams with no dispatches in the period.
 */
class TeamDispatchReportService
{
    /**
     * @return Collection<int, array{team: string, team_lead: string, is_active: bool, members: int, dispatches: int, cancelled: int, incidents: int, completed: int, average_dispatch_minutes: int|null}>
     */
    public function summarize(CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        $dispatchesByTeam = IncidentDispatch::query()
            ->with('incident:id,status,reported_at,created_at')
            ->where('dispatched_at', '>=', $from->format('Y-m-d H:i:s'))
            ->where('dispatched_at', '<', $until->format('Y-m-d H:i:s'))
            ->get()
            ->groupBy('team_id');

        return Team::query()
            ->with('teamLead')
            ->withCount('members')
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team) => $this->teamRow($team, $dispatchesByTeam->get($team->id, collect())));
    }

    /**
     * @param  Collection<int, IncidentDispatch>  $dispatches
     * @return array<string, mixed>
     */
    private function teamRow(Team $team, Collection $dispatches): array
    {
        $active = $dispatches->filter->isActive();
        $incidents = $dispatches->pluck('incident')->filter()->unique('id');

        // Minutes from the incident being reported to this team being dispatched.
        $minutesToDispatch = $dispatches
            ->filter(fn (IncidentDispatch $dispatch) => $dispatch->incident !== null)
            ->map(function (IncidentDispatch $dispatch): float {
                $reportedAt = $dispatch->incident->reported_at ?? $dispatch->incident->created_at;

                return max(0, $reportedAt->diffInMinutes($dispatch->dispatched_at));
            });

        return [
            'team' => $team->name,
            'team_lead' => $team->teamLead?->name ?? 'Not assigned',
            'is_active' => $team->is_active,
            'members' => (int) $team->members_count,
            'dispatches' => $active->count(),
            'cancelled' => $dispatches->count() - $active->count(),
            'incidents' => $incidents->count(),
            'completed' => $incidents->where('status', 'Completed')->count(),
            'average_dispatch_minutes' => $minutesToDispatch->isEmpty() ? null : (int) round($minutesToDispatch->avg()),
        ];
    }
}
