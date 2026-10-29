<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $volunteerQuery = User::query()->whereRelation('role', 'name', 'volunteer');

        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo): CarbonImmutable => CarbonImmutable::now()
                ->startOfMonth()
                ->subMonths($monthsAgo));
        $firstMonth = $months->first();
        $lastMonth = $months->last()->endOfMonth();
        $monthlyCounts = Incident::query()
            ->where(function ($query) use ($firstMonth, $lastMonth): void {
                $query->whereBetween('reported_at', [$firstMonth, $lastMonth])
                    ->orWhere(function ($query) use ($firstMonth, $lastMonth): void {
                        $query->whereNull('reported_at')
                            ->whereBetween('created_at', [$firstMonth, $lastMonth]);
                    });
            })
            ->get(['reported_at', 'created_at'])
            ->countBy(fn (Incident $incident): string => ($incident->reported_at ?? $incident->created_at)
                ->format('Y-m'));
        $incidentTrend = $months->map(fn (CarbonImmutable $month): array => [
            'label' => $month->format('M'),
            'total' => $monthlyCounts->get($month->format('Y-m'), 0),
        ]);

        $statusBreakdown = Incident::query()
            ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
            ->selectRaw("COALESCE(NULLIF(incident_statuses.name, ''), NULLIF(incidents.status, ''), ?) as label, COUNT(*) as total", ['Unassigned'])
            ->groupBy('label')
            ->orderByDesc('total')
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'value' => (int) $row->total,
            ])
            ->values();

        $categoryBreakdown = Incident::query()
            ->leftJoin('incident_categories', 'incident_categories.id', '=', 'incidents.category_id')
            ->selectRaw("COALESCE(NULLIF(incident_categories.name, ''), NULLIF(incidents.category, ''), ?) as label, COUNT(*) as total", ['Other'])
            ->groupBy('label')
            ->orderByDesc('total')
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'value' => (int) $row->total,
            ])
            ->values();

        $severityBreakdown = Incident::query()
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'incidents.severity_id')
            ->selectRaw("COALESCE(NULLIF(severity_levels.name, ''), NULLIF(incidents.severity, ''), ?) as label, COUNT(*) as total", ['Unspecified'])
            ->groupBy('label')
            ->orderByDesc('total')
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'value' => (int) $row->total,
            ])
            ->values();

        $recentIncidents = Incident::query()
            ->leftJoin('incident_categories', 'incident_categories.id', '=', 'incidents.category_id')
            ->leftJoin('barangays', 'barangays.id', '=', 'incidents.barangay_id')
            ->leftJoin('severity_levels', 'severity_levels.id', '=', 'incidents.severity_id')
            ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
            ->select('incidents.*')
            ->selectRaw("COALESCE(NULLIF(incident_categories.name, ''), NULLIF(incidents.category, '')) as category")
            ->selectRaw("COALESCE(NULLIF(barangays.name, ''), NULLIF(incidents.barangay, '')) as barangay")
            ->selectRaw("COALESCE(NULLIF(severity_levels.name, ''), NULLIF(incidents.severity, '')) as severity")
            ->selectRaw("COALESCE(NULLIF(incident_statuses.name, ''), NULLIF(incidents.status, '')) as status")
            ->orderByRaw('COALESCE(incidents.reported_at, incidents.created_at) DESC')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalVolunteers' => (clone $volunteerQuery)->count(),
            'verifiedVolunteers' => (clone $volunteerQuery)->whereHas('volunteerProfile', fn ($query) => $query->where('verification_status', 'verified'))->count(),
            'pendingVolunteerVerification' => (clone $volunteerQuery)->where(function ($query) {
                $query->whereDoesntHave('volunteerProfile')
                    ->orWhereHas('volunteerProfile', fn ($profileQuery) => $profileQuery->where('verification_status', '!=', 'verified'));
            })->count(),
            'totalDispatchers' => User::query()->whereRelation('role', 'name', 'dispatcher')->count(),
            'totalIncidents' => Incident::query()->count(),
            'openIncidents' => Incident::query()
                ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
                ->whereRaw(
                    "COALESCE(NULLIF(incident_statuses.name, ''), NULLIF(incidents.status, '')) IS NULL
                    OR COALESCE(NULLIF(incident_statuses.name, ''), NULLIF(incidents.status, '')) != ?",
                    [Incident::STATUS_COMPLETED]
                )
                ->count(),
            'completedIncidents' => Incident::query()
                ->leftJoin('incident_statuses', 'incident_statuses.id', '=', 'incidents.status_id')
                ->whereRaw(
                    "COALESCE(NULLIF(incident_statuses.name, ''), NULLIF(incidents.status, '')) = ?",
                    [Incident::STATUS_COMPLETED]
                )
                ->count(),
            'statusBreakdown' => $statusBreakdown,
            'categoryBreakdown' => $categoryBreakdown,
            'severityBreakdown' => $severityBreakdown,
            'incidentTrend' => $incidentTrend,
            'trendMaximum' => max(1, $incidentTrend->max('total')),
            'trendTotal' => max(1, $incidentTrend->sum('total')),
            'recentIncidents' => $recentIncidents,
        ]);
    }
}
