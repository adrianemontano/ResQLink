<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $volunteer = $request->user();
        abort_unless($volunteer instanceof User, 403);

        // Single query for counts by status instead of multiple count() calls
        $statusCounts = $this->incidentsFor($volunteer)
            ->selectRaw("COUNT(*) as total, SUM(status = ?) as completed", [Incident::STATUS_COMPLETED])
            ->first();

        $totalReports = (int) $statusCounts->total;
        $completedReports = (int) $statusCounts->completed;

        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo): CarbonImmutable => CarbonImmutable::now()
                ->startOfMonth()
                ->subMonths($monthsAgo));
        $firstMonth = $months->first();
        $lastMonth = $months->last()->endOfMonth();

        // Use SQL GROUP BY instead of fetching all rows and counting in PHP
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', COALESCE(reported_at, created_at))"
            : "DATE_FORMAT(COALESCE(reported_at, created_at), '%Y-%m')";
        $monthlyCounts = $this->incidentsFor($volunteer)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as total")
            ->where(function (Builder $query) use ($firstMonth, $lastMonth): void {
                $query->whereBetween('reported_at', [$firstMonth, $lastMonth])
                    ->orWhere(function (Builder $query) use ($firstMonth, $lastMonth): void {
                        $query->whereNull('reported_at')
                            ->whereBetween('created_at', [$firstMonth, $lastMonth]);
                    });
            })
            ->groupBy('month')
            ->pluck('total', 'month');

        $reportTrend = $months->map(fn (CarbonImmutable $month): array => [
            'label' => $month->format('M'),
            'total' => (int) $monthlyCounts->get($month->format('Y-m'), 0),
        ]);

        // Use SQL GROUP BY instead of fetching all rows and counting in PHP
        $categoryBreakdown = $this->incidentsFor($volunteer)
            ->selectRaw("COALESCE(NULLIF(category, ''), 'Other') as name, COUNT(*) as `count`")
            ->groupBy('name')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row): array => [
                'name' => $row->name,
                'count' => (int) $row->count,
                'percentage' => $totalReports === 0 ? 0 : (int) round($row->count / $totalReports * 100),
            ]);

        $recentIncidents = $this->incidentsFor($volunteer)
            ->orderByDesc('reported_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('volunteer.dashboard', [
            'totalReports' => $totalReports,
            'activeReports' => $totalReports - $completedReports,
            'completedReports' => $completedReports,
            'reportTrend' => $reportTrend,
            'trendMaximum' => max(1, $reportTrend->max('total')),
            'trendTotal' => max(1, $reportTrend->sum('total')),
            'categoryBreakdown' => $categoryBreakdown,
            'recentIncidents' => $recentIncidents,
        ]);
    }

    /**
     * @return Builder<Incident>
     */
    private function incidentsFor(User $volunteer): Builder
    {
        return Incident::query()->where(function (Builder $query) use ($volunteer): void {
            $query->where('volunteer_id', $volunteer->getKey())
                ->orWhere('reported_by', $volunteer->getKey());
        });
    }
}
