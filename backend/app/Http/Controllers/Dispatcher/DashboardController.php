<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $requestedPerPage = request()->query('per_page');
        $perPage = in_array($requestedPerPage, ['10', '25', '50'], true)
            ? (int) $requestedPerPage
            : 10;
        $statusOptions = [
            'all' => 'All',
            'reported' => 'Reported',
            'received' => 'Received',
            'dispatched' => 'Dispatched',
            'completed' => 'Completed',
        ];
        $requestedStatus = request()->query('status', 'all');
        $selectedStatus = is_string($requestedStatus) ? strtolower($requestedStatus) : 'all';
        $selectedStatus = array_key_exists($selectedStatus, $statusOptions) ? $selectedStatus : 'all';
        $statusCounts = Incident::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $statusFilters = collect($statusOptions)->map(
            fn (string $label, string $slug): array => [
                'slug' => $slug,
                'label' => $label,
                'count' => $slug === 'all' ? $statusCounts->sum() : (int) $statusCounts->get($label, 0),
            ],
        );

        $incidents = Incident::query()
            ->with(['reporter.volunteerProfile'])
            ->when(
                $selectedStatus !== 'all',
                fn ($query) => $query->where('status', $statusOptions[$selectedStatus]),
            )
            ->orderByRaw("CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Moderate' THEN 3 WHEN 'Low' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE status WHEN 'Reported' THEN 1 WHEN 'Received' THEN 2 WHEN 'Dispatched' THEN 3 ELSE 4 END")
            ->orderByDesc('reported_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('dispatcher.dashboard', [
            'activeIncidents' => Incident::query()->whereIn('status', ['Reported', 'Received', 'Dispatched'])->count(),
            'completedIncidents' => Incident::query()->where('status', 'Completed')->count(),
            'incidents' => $incidents,
            'statusFilters' => $statusFilters,
            'selectedStatus' => $selectedStatus,
            'perPage' => $perPage,
        ]);
    }
}
