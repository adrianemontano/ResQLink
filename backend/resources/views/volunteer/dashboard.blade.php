@extends('layouts.dashboard', ['title' => 'Volunteer Dashboard | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Volunteer Dashboard</h1>
            <p>Submit a complete incident report for dispatcher review.</p>
        </div>
        <a class="button" href="{{ route('volunteer.incidents.create') }}">Submit Incident</a>
    </div>

    <section class="card incident-intro">
        <span class="eyebrow">Verified volunteer access</span>
        <h2>Report an emergency situation</h2>
        <p>Include the location, affected population, and impact radius so the response team can assess it quickly.</p>
        <a class="button" href="{{ route('volunteer.incidents.create') }}">Create Incident Report</a>
    </section>
@endsection
