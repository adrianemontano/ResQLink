@extends('layouts.dashboard', ['title' => 'Incident Map | ResQLink'])

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
    <script>
        const incidentMapData = @json($incidents);
    </script>
    <script src="{{ asset('js/dispatcher-map.js') }}"></script>
@endpush
