@extends('layouts.dashboard', ['title' => 'Incident Map | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Map</h1>
            <p>Live incident locations reported by volunteers.</p>
        </div>
    </div>

    <div class="map-legend">
        <span class="legend-item"><span class="legend-dot dot-pending"></span>Reported</span>
        <span class="legend-item"><span class="legend-dot dot-received"></span>Received</span>
        <span class="legend-item"><span class="legend-dot dot-dispatched"></span>Dispatched</span>
        <span class="legend-item"><span class="legend-dot dot-completed"></span>Completed</span>
    </div>

    <x-incident-map />
@endsection
