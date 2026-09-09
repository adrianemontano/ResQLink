@extends('layouts.dashboard', ['title' => 'Incident Map | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Map</h1>
            <p>Live incident locations reported by volunteers.</p>
        </div>
    </div>

    <x-incident-map />
@endsection
