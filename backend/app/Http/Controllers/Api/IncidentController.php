<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncidentRequest;
use App\Services\IncidentSubmissionService;
use Illuminate\Http\JsonResponse;

class IncidentController extends Controller
{
    public function store(
        StoreIncidentRequest $request,
        IncidentSubmissionService $submissionService,
    ): JsonResponse {
        $data = $request->validated();
        $incident = $submissionService->submit($data, $request->user());

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
