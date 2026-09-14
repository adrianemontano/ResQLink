<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;

class DispatchPointController extends Controller
{
    public function index(): JsonResponse
    {
        $incidents = Incident::query()
            ->with('reporter')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest('reported_at')
            ->get()
            ->map(fn (Incident $incident): array => [
                'id' => $incident->id,
                'type' => 'incident',
                'category' => $incident->category,
                'reporter' => $incident->reporter?->name ?? 'Unknown',
                'status' => $incident->status,
                'severity' => $incident->severity,
                'reported_at' => $incident->reported_at?->toISOString(),
                'latitude' => (float) $incident->latitude,
                'longitude' => (float) $incident->longitude,
                'impact_radius' => (float) ($incident->impact_radius ?? 0),
                'barangay' => $incident->barangay,
                'detail_url' => route('dispatcher.incidents.show', $incident),
            ]);

        return response()->json(['data' => $incidents]);
    }

}