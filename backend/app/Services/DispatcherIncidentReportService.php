<?php

namespace App\Services;

use App\Models\Incident;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class DispatcherIncidentReportService
{
    /** @return array<string, mixed> */
    public function generate(int $dispatcherId, array $filters): array
    {
        $timezone = 'Asia/Manila';
        $from = CarbonImmutable::parse($filters['from'], $timezone)->startOfDay();
        $to = CarbonImmutable::parse($filters['to'], $timezone)->endOfDay();
        $incidents = $this->query($dispatcherId, $filters)
            ->whereRaw('COALESCE(incidents.reported_at, incidents.created_at) BETWEEN ? AND ?', [
                $from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s'),
            ])
            ->with(['reporter', 'history.status', 'history.dispatcher'])
            ->orderByDesc('reported_at')->get();

        $rows = $incidents->map(fn (Incident $incident): array => $this->row($incident, $timezone));
        $resolved = $rows->pluck('resolution_minutes')->filter(fn ($value) => $value !== null)->sort()->values();
        $totalMinutes = $resolved->sum();
        $groupBy = $filters['group_by'] ?? 'day';

        return [
            'filters' => $filters, 'from' => $from, 'to' => $to,
            'generatedAt' => CarbonImmutable::now($timezone),
            'dispatcher' => auth()->user()->name,
            'incidents' => $incidents, 'rows' => $rows,
            'total' => $incidents->count(), 'resolvedCount' => $resolved->count(),
            'averageMinutes' => $resolved->count() ? (int) round($totalMinutes / $resolved->count()) : null,
            'fastestMinutes' => $resolved->first(), 'slowestMinutes' => $resolved->last(),
            'byStatus' => $incidents->countBy('status'),
            'byCategory' => $incidents->countBy('category'),
            'bySeverity' => $incidents->countBy('severity'),
            'byBarangay' => $incidents->countBy('barangay'),
            'groupBy' => $groupBy,
            'timeline' => $incidents->groupBy(fn (Incident $incident) => $this->periodLabel($incident, $groupBy, $timezone))->map->count(),
            'categories' => Incident::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'barangays' => Incident::query()->whereNotNull('barangay')->distinct()->orderBy('barangay')->pluck('barangay'),
        ];
    }

    /** @return Builder<Incident> */
    private function query(int $dispatcherId, array $filters): Builder
    {
        return Incident::query()
            ->whereHas('history', fn ($query) => $query->where('changed_by', $dispatcherId))
            ->when($filters['category'] ?? null, fn ($query, $value) => $query->where('category', $value))
            ->when($filters['severity'] ?? null, fn ($query, $value) => $query->where('severity', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['barangay'] ?? null, fn ($query, $value) => $query->where('barangay', $value));
    }

    private function periodLabel(Incident $incident, string $groupBy, string $timezone): string
    {
        $date = ($incident->reported_at ?? $incident->created_at)?->timezone($timezone);
        if ($date === null) return 'Unknown';

        return match ($groupBy) {
            'week' => $date->startOfWeek()->format('M d, Y'),
            'month' => $date->startOfMonth()->format('M Y'),
            default => $date->toDateString(),
        };
    }

    /** @return array<string, mixed> */
    private function row(Incident $incident, string $timezone): array
    {
        $times = $incident->history->sortBy('changed_at')->mapWithKeys(fn ($entry) => [
            $entry->status?->name => $entry->changed_at?->timezone($timezone),
        ]);
        $reported = $times->get('Reported') ?? $incident->reported_at?->timezone($timezone);
        $received = $times->get('Received');
        $dispatched = $times->get('Dispatched');
        $completed = $times->get('Completed');
        $minutes = static fn ($start, $end) => $start && $end ? (int) $start->diffInMinutes($end) : null;

        return [
            'id' => $incident->id, 'reference' => 'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT),
            'category' => $incident->category, 'severity' => $incident->severity, 'status' => $incident->status,
            'barangay' => $incident->barangay, 'reporter' => $incident->reporter?->name ?? 'Unknown',
            'reported_at' => $reported, 'received_at' => $received, 'dispatched_at' => $dispatched, 'completed_at' => $completed,
            'reported_received_minutes' => $minutes($reported, $received),
            'received_dispatched_minutes' => $minutes($received, $dispatched),
            'dispatched_completed_minutes' => $minutes($dispatched, $completed),
            'resolution_minutes' => $minutes($reported, $completed),
            'history' => $incident->history->sortBy('changed_at')->map(fn ($entry) => [
                'status' => $entry->status?->name ?? 'Unknown', 'who' => $entry->dispatcher?->name ?? 'Unknown',
                'at' => $entry->changed_at?->timezone($timezone),
            ])->values(),
        ];
    }
}
