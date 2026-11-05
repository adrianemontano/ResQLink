<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispatcher\UpdateIncidentStatusRequest;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncidentController extends Controller
{
    private const ALLOWED_TRANSITIONS = [
        'Reported' => ['Received'],
        'Received' => ['Dispatched'],
        'Dispatched' => ['Completed'],
        'Completed' => [],
    ];

    public function index(): View
    {
        $baseQuery = Incident::query()
            ->when(request('category'), fn ($query, $category) => $query->where('category', $category))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('barangay'), fn ($query, $barangay) => $query->where('barangay', $barangay));

        $incidents = (clone $baseQuery)
            ->with(['reporter.volunteerProfile', 'photos'])
            ->orderByRaw("CASE severity WHEN 'Critical' THEN 1 WHEN 'High' THEN 2 WHEN 'Moderate' THEN 3 WHEN 'Low' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE status WHEN 'Reported' THEN 1 WHEN 'Received' THEN 2 WHEN 'Dispatched' THEN 3 WHEN 'Completed' THEN 4 ELSE 5 END")
            ->orderByDesc('reported_at')
            ->paginate(15)
            ->withQueryString();

        if (request()->ajax()) {
            return view('dispatcher.incidents.partials.results', compact('incidents'));
        }

        return view('dispatcher.incidents.index', [
            'incidents' => $incidents,
            'categories' => (clone $baseQuery)->select('category')->distinct()->orderBy('category')->pluck('category'),
            'statuses' => ['Reported', 'Received', 'Dispatched', 'Completed'],
            'barangays' => request()->hasAny(['category', 'status', 'barangay'])
                ? (clone $baseQuery)->select('barangay')->distinct()->orderBy('barangay')->pluck('barangay')->filter()->values()->all()
                : $this->mapBarangays(),
        ]);
    }

    public function show(Incident $incident): View
    {
        $incident->load(['reporter.volunteerProfile', 'photos', 'history.dispatcher', 'history.status']);

        return view('dispatcher.incidents.show', compact('incident'));
    }

    public function updateStatus(
        UpdateIncidentStatusRequest $request,
        Incident $incident,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $status = $data['status'];

        $change = DB::transaction(function () use ($incident, $data, $request, $status): ?array {
            $lockedIncident = Incident::query()->lockForUpdate()->findOrFail($incident->id);

            if (! $this->isValidTransition($lockedIncident->status, $status)) {
                return null;
            }

            $statusId = DB::table('incident_statuses')->where('name', $status)->value('id');
            $changedAt = now();
            $lockedIncident->update([
                'status' => $status,
                'status_id' => $statusId,
                'dispatched_at' => $status === 'Dispatched' ? $changedAt : $lockedIncident->dispatched_at,
                'completed_at' => $status === 'Completed' ? $changedAt : $lockedIncident->completed_at,
            ]);

            $lockedIncident->history()->create([
                'status_id' => $statusId,
                'changed_by' => $request->user()->id,
                'notes' => $data['notes'],
                'changed_at' => $changedAt,
            ]);

            return [
                'dispatcher' => $request->user()->name,
                'changed_at' => $changedAt->timezone('Asia/Manila')->format('M d, Y, h:i A'),
            ];
        });

        if ($change === null) {
            $message = $this->invalidTransitionMessage($incident->fresh()->status);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('dispatcher.incidents.show', $incident)
                ->withErrors(['status' => $message])
                ->withInput();
        }

        $message = 'Incident status updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $status,
                'dispatcher' => $change['dispatcher'],
                'changed_at' => $change['changed_at'],
                'notes' => $data['notes'],
            ]);
        }

        return redirect()->route('dispatcher.incidents.show', $incident)
            ->with('incident_status_success', $message);
    }

    public function map(): View
    {
        return view('dispatcher.map');
    }

    public function mapIncidents(): JsonResponse
    {
        $features = Incident::query()
            ->with('reporter:id,name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('reported_at')
            ->get()
            ->map(fn (Incident $incident): array => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $incident->longitude, (float) $incident->latitude],
                ],
                'properties' => [
                    'id' => $incident->id,
                    'reference' => 'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT),
                    'status' => $incident->status ?? 'Reported',
                    'severity' => $incident->severity ?? 'Unrated',
                    'reporter' => $incident->reporter?->name ?? 'Unknown',
                    'category' => $incident->category ?? 'Unspecified',
                    'barangay' => $incident->barangay ?? 'Unknown',
                    'affected_population' => $incident->affected_population ?? $incident->persons_count ?? 0,
                    'impact_radius' => (float) ($incident->impact_radius ?? 0),
                    'detail_url' => route('dispatcher.incidents.show', $incident),
                ],
            ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    private function mapBarangays(): array
    {
        $contents = file_get_contents(public_path('maps/cebu-city-barangays.geojson'));
        $features = json_decode($contents ?: '', true)['features'] ?? [];

        $barangays = array_map(
            fn (array $feature): string => $feature['properties']['ADM4_EN'] ?? '',
            $features,
        );

        $barangays = array_values(array_unique(array_filter($barangays)));
        sort($barangays, SORT_NATURAL | SORT_FLAG_CASE);

        return $barangays;
    }

    private function isValidTransition(?string $currentStatus, string $nextStatus): bool
    {
        return in_array($nextStatus, self::ALLOWED_TRANSITIONS[$currentStatus] ?? [], true);
    }

    private function invalidTransitionMessage(?string $currentStatus): string
    {
        $label = $currentStatus ?? 'This';
        $next = array_values(array_diff(self::ALLOWED_TRANSITIONS[$currentStatus] ?? [], [$currentStatus]));

        return $next === []
            ? "{$label} incidents can no longer change status."
            : "{$label} incidents can only move to {$next[0]} next.";
    }

}
