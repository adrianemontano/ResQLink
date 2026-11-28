<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\ResponseTeam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamAvailabilityController extends Controller
{
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} min" . ($minutes === 1 ? '' : 's');
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        $hrLabel = "{$hours} hr" . ($hours === 1 ? '' : 's');

        return $mins > 0 ? "{$hrLabel} {$mins} min" . ($mins === 1 ? '' : 's') : $hrLabel;
    }

    private function buildTeamData(ResponseTeam $team): array
    {
        $activeDispatch = Incident::query()
            ->where('dispatched_team_id', $team->id)
            ->where('status', 'Dispatched')
            ->first();

        $allDispatches = Incident::query()
            ->where('dispatched_team_id', $team->id)
            ->whereIn('status', ['Dispatched', 'Completed'])
            ->orderByDesc('dispatched_at')
            ->orderByDesc('id')
            ->get(['id', 'status', 'category', 'barangay', 'dispatched_at', 'completed_at']);

        $completedWithTimes = $allDispatches->filter(
            fn ($i) => $i->status === 'Completed' && $i->dispatched_at && $i->completed_at
        );

        $avgMinutes = $completedWithTimes->count() > 0
            ? (int) round($completedWithTimes->avg(fn ($i) => $i->dispatched_at->diffInMinutes($i->completed_at)))
            : null;

        if (! $team->is_active) {
            $status = 'inactive';
        } elseif ($activeDispatch) {
            $status = 'on_mission';
        } else {
            $status = 'available';
        }

        $members = collect(preg_split('/[\r\n,]+/', (string) ($team->members ?? '')))
            ->map(fn ($m) => trim($m))
            ->filter()
            ->values()
            ->all();

        $activityLog = $allDispatches->map(function ($i) {
            $minutes = ($i->dispatched_at && $i->completed_at)
                ? (int) $i->dispatched_at->diffInMinutes($i->completed_at)
                : null;

            return [
                'id'           => $i->id,
                'inc_no'       => 'INC-' . str_pad($i->id, 4, '0', STR_PAD_LEFT),
                'status'       => $i->status,
                'category'     => $i->category ?? '—',
                'barangay'     => $i->barangay ?? '—',
                'responded_on' => $i->dispatched_at?->timezone('Asia/Manila')->format('M d, Y, h:i A') ?? '—',
                'completed_on' => $i->completed_at?->timezone('Asia/Manila')->format('M d, Y, h:i A') ?? ($i->status === 'Dispatched' ? 'In Progress' : '—'),
                'duration'     => $minutes !== null ? $this->formatDuration($minutes) : '—',
            ];
        })->values()->all();

        return [
            'id'              => $team->id,
            'name'            => $team->name,
            'type'            => $team->type,
            'team_leader'     => $team->team_leader ?: '—',
            'contact_number'  => $team->contact_number ?: '—',
            'members'         => $members,
            'is_active'       => $team->is_active,
            'status'          => $status,
            'active_inc_no'   => $activeDispatch ? 'INC-' . str_pad($activeDispatch->id, 4, '0', STR_PAD_LEFT) : null,
            'total_responses' => $completedWithTimes->count(),
            'avg_duration'    => $avgMinutes !== null ? $this->formatDuration($avgMinutes) : '—',
            'activity_log'    => $activityLog,
        ];
    }

    public function index(Request $request): View
    {
        $requestedPerPage = $request->query('per_page');
        $perPage = in_array($requestedPerPage, ['10', '25', '50'], true)
            ? (int) $requestedPerPage
            : 10;
        $requestedStatus = $request->query('status', 'all');
        $allowedStatuses = ['all', 'available', 'on_mission', 'inactive'];
        $selectedStatus = is_string($requestedStatus) && in_array($requestedStatus, $allowedStatuses, true)
            ? $requestedStatus
            : 'all';
        $activeTeamIds = Incident::query()
            ->where('status', 'Dispatched')
            ->whereNotNull('dispatched_team_id')
            ->distinct()
            ->pluck('dispatched_team_id')
            ->all();

        $teamCount = ResponseTeam::query()->count();
        $inactiveCount = ResponseTeam::query()->where('is_active', false)->count();
        $onMissionCount = ResponseTeam::query()
            ->where('is_active', true)
            ->whereIn('id', $activeTeamIds)
            ->count();
        $summary = [
            'all' => $teamCount,
            'available' => $teamCount - $inactiveCount - $onMissionCount,
            'on_mission' => $onMissionCount,
            'inactive' => $inactiveCount,
        ];

        $query = ResponseTeam::query()->orderBy('name');
        if ($selectedStatus === 'available') {
            $query->where('is_active', true);
            if ($activeTeamIds !== []) {
                $query->whereNotIn('id', $activeTeamIds);
            }
        } elseif ($selectedStatus === 'on_mission') {
            $query->where('is_active', true)->whereIn('id', $activeTeamIds);
        } elseif ($selectedStatus === 'inactive') {
            $query->where('is_active', false);
        }

        $teams = $query->paginate($perPage)->withQueryString();
        $teams->setCollection(
            $teams->getCollection()->map(fn (ResponseTeam $team): array => $this->buildTeamData($team)),
        );

        return view('admin.teams.availability', compact('teams', 'summary', 'perPage', 'selectedStatus'));
    }

    public function show(ResponseTeam $team): View
    {
        $teamData = $this->buildTeamData($team);

        return view('admin.teams.availability-show', ['team' => $teamData]);
    }
}
