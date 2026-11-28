<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class IncidentMapController extends Controller
{
    public function map(): View
    {
        return view('dispatcher.map');
    }

    public function mapIncidents(): JsonResponse
    {
        $features = Incident::query()
            ->select([
                'id',
                'reported_by',
                'status',
                'severity',
                'category',
                'barangay',
                'affected_population',
                'persons_count',
                'impact_radius',
                'latitude',
                'longitude',
                'reported_at',
            ])
            ->with('reporter:id,name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('reported_at')
            ->limit(500)
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

    public function mapBarangays(): array
    {
        return Cache::remember('cebu_city_barangays', 3600, function (): array {
            $contents = file_get_contents(public_path('maps/cebu-city-barangays.geojson'));
            $features = json_decode($contents ?: '', true)['features'] ?? [];

            $barangays = array_map(
                fn (array $feature): string => $feature['properties']['ADM4_EN'] ?? '',
                $features,
            );

            $barangays = array_values(array_unique(array_filter($barangays)));
            sort($barangays, SORT_NATURAL | SORT_FLAG_CASE);

            return $barangays;
        });
    }
}
