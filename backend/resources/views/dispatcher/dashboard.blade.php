@extends('layouts.dashboard', ['title' => 'Dispatcher Dashboard | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Dispatcher Dashboard</h1>
            <p>Initial incident queue overview for dispatch operations.</p>
        </div>
    </div>

    <section class="grid">
        <article class="card stat-red">
            <h2>Active Incidents</h2>
            <div class="metric">{{ $activeIncidents }}</div>
        </article>
        <article class="card stat-amber">
            <h2>Pending Incidents</h2>
            <div class="metric">{{ $pendingIncidents }}</div>
        </article>
    </section>
@endsection
