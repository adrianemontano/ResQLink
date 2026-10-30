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

        $totalReports = $this->incidentsFor($volunteer)->count();
        $completedReports = $this->incidentsFor($volunteer)
            ->where('status', Incident::STATUS_COMPLETED)
            ->count();

        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo): CarbonImmutable => CarbonImmutable::now()
                ->startOfMonth()
                ->subMonths($monthsAgo));
        $firstMonth = $months->first();
        $lastMonth = $months->last()->endOfMonth();

        $monthlyCounts = $this->incidentsFor($volunteer)
            ->where(function (Builder $query) use ($firstMonth, $lastMonth): void {
                $query->whereBetween('reported_at', [$firstMonth, $lastMonth])
                    ->orWhere(function (Builder $query) use ($firstMonth, $lastMonth): void {
                        $query->whereNull('reported_at')
                            ->whereBetween('created_at', [$firstMonth, $lastMonth]);
                    });
            })
            ->get(['reported_at', 'created_at'])
            ->countBy(fn (Incident $incident): string => ($incident->reported_at ?? $incident->created_at)
                ->format('Y-m'));

        $reportTrend = $months->map(fn (CarbonImmutable $month): array => [
            'label' => $month->format('M'),
            'total' => $monthlyCounts->get($month->format('Y-m'), 0),
        ]);

        $categoryBreakdown = $this->incidentsFor($volunteer)
            ->get(['category'])
            ->countBy(fn (Incident $incident): string => $incident->category ?: 'Other')
            ->sortDesc()
            ->map(fn (int $count, string $category): array => [
                'name' => $category,
                'count' => $count,
                'percentage' => $totalReports === 0 ? 0 : (int) round($count / $totalReports * 100),
            ])
            ->values();

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
