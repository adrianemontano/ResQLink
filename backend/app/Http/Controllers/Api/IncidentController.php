<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Models\Incident;
use App\Services\IncidentSeverityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IncidentController extends Controller
{
    public function store(
        StoreIncidentRequest $request,
        IncidentSeverityService $severityService,
    ): JsonResponse {
        $data = $request->validated();
        $severity = $severityService->classify(
            (int) $data['affected_population'],
            (float) $data['impact_radius'],
        );

        $incident = DB::transaction(function () use ($data, $request, $severity): Incident {
            $categoryId = DB::table('incident_categories')
                ->where('name', $data['category'])
                ->value('id');
            $severityId = DB::table('severity_levels')
                ->where('name', $severity)
                ->value('id');
            $statusId = DB::table('incident_statuses')
                ->where('name', 'Reported')
                ->value('id');

            $incident = Incident::create([
                ...$data,
                'volunteer_id' => $request->user()->id,
                'reported_by' => $request->user()->id,
                'category_id' => $categoryId,
                'severity_id' => $severityId,
                'status_id' => $statusId,
                'category' => $data['category'],
                'barangay' => $data['barangay'],
                'nearest_landmark' => $data['nearest_landmark'],
                'landmark' => $data['nearest_landmark'],
                'persons_count' => $data['affected_population'],
                'severity' => $severity,
                'status' => 'Reported',
                'reported_at' => now(),
            ]);

            return $incident->fresh();
        });

        return response()->json([
            'data' => [
                'id' => $incident->id,
                'identifier' => 'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT),
                'reported_at' => $incident->reported_at?->toISOString(),
                'status' => $incident->status,
                'severity' => $incident->severity,
                'category' => $incident->category,
                'barangay' => $incident->barangay,
                'nearest_landmark' => $incident->nearest_landmark,
                'affected_population' => $incident->affected_population,
                'latitude' => $incident->latitude,
                'longitude' => $incident->longitude,
                'impact_radius' => $incident->impact_radius,
                'notes' => $incident->notes,
            ],
        ], 201);
    }
}
