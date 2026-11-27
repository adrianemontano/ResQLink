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
            // Newest incidents first (2026-11-27 feedback); severity stays visible as a badge.
            ->orderByRaw('COALESCE(reported_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $activeIncidents = (int) $statusCounts->get('Reported', 0)
            + (int) $statusCounts->get('Received', 0)
            + (int) $statusCounts->get('Dispatched', 0);
        $completedIncidents = (int) $statusCounts->get('Completed', 0);

        return view('dispatcher.dashboard', [
            'activeIncidents' => $activeIncidents,
            'completedIncidents' => $completedIncidents,
            'incidents' => $incidents,
            'statusFilters' => $statusFilters,
            'selectedStatus' => $selectedStatus,
            'perPage' => $perPage,
        ]);
    }
}
