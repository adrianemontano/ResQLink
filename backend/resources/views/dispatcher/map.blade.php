@extends('layouts.dashboard', ['title' => 'Incident Map | ResQLink'])

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Map</h1>
            <p>Live incident locations reported by volunteers.</p>
        </div>
    </div>

    <div class="map-legend">
        <span class="legend-item"><span class="legend-dot dot-pending"></span>Pending</span>
        <span class="legend-item"><span class="legend-dot dot-received"></span>Received</span>
        <span class="legend-item"><span class="legend-dot dot-dispatched"></span>Dispatched</span>
    </div>

    <div id="incident-map"></div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const incidentMapData = @json($incidents);
    </script>
    <script src="{{ asset('js/dispatcher-map.js') }}"></script>
@endpush
