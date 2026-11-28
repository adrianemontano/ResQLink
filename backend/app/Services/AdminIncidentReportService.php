<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentHistory;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminIncidentReportService
{
    /**
     * Build report totals and distributions for one application-timezone window.
     *
     * @return array<string, mixed>
     */
    public function generate(string $period, CarbonImmutable $selectedDate): array
    {
        $timezone = config('app.timezone', 'UTC');
        [$from, $until] = $this->periodBounds($period, $selectedDate->setTimezone($timezone));
        $scope = $this->incidentScope($from, $until);
        $total = (clone $scope)->count();

        return [
            'period' => $period,
            'date' => $selectedDate->toDateString(),
            'timezone' => $timezone,
            'from' => $from,
            'until' => $until,
            'totalIncidents' => $total,
            'byCategory' => $this->distribution(clone $scope, 'category'),
            'byBarangay' => $this->distribution(clone $scope, 'barangay'),
            'byStatus' => $this->distribution(clone $scope, 'status'),
            'bySeverity' => $this->distribution(clone $scope, 'severity'),
            'byTeam' => $this->teamDispatches($from, $until),
            'responseMetrics' => $this->responseMetrics(clone $scope),
            'byFrequency' => $this->frequency(clone $scope, $from, $until),
            'hasIncidents' => $total > 0,
        ];
    }

    /**
     * @return Collection<int, object{label: string, total: int}>
     */
    private function teamDispatches(CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        return IncidentHistory::query()
            ->join('response_teams', 'response_teams.id', '=', 'incident_histories.response_team_id')
            ->where('incident_histories.changed_at', '>=', $from->format('Y-m-d H:i:s'))
            ->where('incident_histories.changed_at', '<', $until->format('Y-m-d H:i:s'))
            ->selectRaw('response_teams.name as label, COUNT(*) as total')
            ->groupBy('response_teams.name')
            ->orderByDesc('total')
            ->orderBy('response_teams.name')
            ->get();
    }

    /**
     * @return array{avgReceiveMinutes: ?int, avgDispatchMinutes: ?int, avgCompleteMinutes: ?int}
     */
    private function responseMetrics(Builder $scope): array
    {
        $rows = $scope
            ->get(['reported_at', 'created_at', 'received_at', 'dispatched_at', 'completed_at']);

        $receiveDiffs = [];
        $dispatchDiffs = [];
        $completeDiffs = [];

        foreach ($rows as $incident) {
            $reported = $incident->reported_at ?? $incident->created_at;
            if (! $reported) {
                continue;
            }
            if ($incident->received_at && $incident->received_at->gte($reported)) {
                $receiveDiffs[] = $reported->diffInMinutes($incident->received_at);
            }
            if ($incident->dispatched_at && $incident->dispatched_at->gte($reported)) {
                $dispatchDiffs[] = $reported->diffInMinutes($incident->dispatched_at);
            }
            if ($incident->completed_at && $incident->completed_at->gte($reported)) {
                $completeDiffs[] = $reported->diffInMinutes($incident->completed_at);
            }
        }

        return [
            'avgReceiveMinutes' => count($receiveDiffs) > 0 ? (int) round(array_sum($receiveDiffs) / count($receiveDiffs)) : null,
            'avgDispatchMinutes' => count($dispatchDiffs) > 0 ? (int) round(array_sum($dispatchDiffs) / count($dispatchDiffs)) : null,
            'avgCompleteMinutes' => count($completeDiffs) > 0 ? (int) round(array_sum($completeDiffs) / count($completeDiffs)) : null,
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function periodBounds(string $period, CarbonImmutable $date): array
    {
        $from = match ($period) {
            'weekly' => $date->startOfWeek(CarbonInterface::MONDAY)->startOfDay(),
            'monthly' => $date->startOfMonth()->startOfDay(),
            default => $date->startOfDay(),
        };
        $until = match ($period) {
            'weekly' => $from->addWeek(),
            'monthly' => $from->addMonth(),
            default => $from->addDay(),
        };

        return [$from, $until];
    }

    private function incidentScope(CarbonImmutable $from, CarbonImmutable $until): Builder
    {
        return Incident::query()
            ->whereRaw('COALESCE(incidents.reported_at, incidents.created_at) >= ?', [$from->format('Y-m-d H:i:s')])
            ->whereRaw('COALESCE(incidents.reported_at, incidents.created_at) < ?', [$until->format('Y-m-d H:i:s')]);
    }

    /**
     * @return Collection<int, object{label: string, total: int}>
     */
    private function distribution(Builder $scope, string $column): Collection
    {
        return $scope
            ->selectRaw("COALESCE(NULLIF({$column}, ''), 'Unknown') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->orderBy('label')
            ->get();
    }

    /**
     * Return every date in the selected window so missing dates display as zero.
     *
     * @return list<array{date: string, total: int}>
     */
    private function frequency(Builder $scope, CarbonImmutable $from, CarbonImmutable $until): array
    {
        $counts = $scope
            ->selectRaw('DATE(COALESCE(reported_at, created_at)) as report_date, COUNT(*) as total')
            ->groupByRaw('DATE(COALESCE(reported_at, created_at))')
            ->pluck('total', 'report_date');
        $frequency = [];

        for ($date = $from; $date->lt($until); $date = $date->addDay()) {
            $key = $date->toDateString();
            $frequency[] = ['date' => $key, 'total' => (int) ($counts[$key] ?? 0)];
        }

        return $frequency;
    }
}
