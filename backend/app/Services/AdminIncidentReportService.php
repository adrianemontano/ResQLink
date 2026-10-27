<?php

namespace App\Services;

use App\Models\Incident;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
            'byFrequency' => $this->frequency(clone $scope, $from, $until),
            'hasIncidents' => $total > 0,
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
