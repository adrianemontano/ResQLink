@extends('layouts.dashboard', ['title' => 'Dispatcher Dashboard | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Dispatcher Dashboard</h1>
            <p>Prioritized incident queue for dispatch operations.</p>
        </div>
    </div>

    <section class="grid">
        <article class="card stat-red">
            <h2>Active Incidents</h2>
            <div class="metric">{{ $activeIncidents }}</div>
        </article>
        <article class="card stat-amber">
            <h2>Completed Incidents</h2>
            <div class="metric">{{ $completedIncidents }}</div>
        </article>
    </section>

    <section class="card">
        <div class="header-row">
            <h2>Active Incident Queue</h2>
            <a class="button" href="{{ route('dispatcher.incidents.index') }}">View all incidents</a>
        </div>
        <table>
            <thead>
                <tr><th>Incident</th><th>Reporter</th><th>Category</th><th>Persons</th><th>Barangay</th><th>Severity</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? 'Unknown' }}</td>
                        <td>{{ $incident->category }}</td>
                        <td>{{ $incident->affected_population ?? $incident->persons_count }}</td>
                        <td>{{ $incident->barangay }}</td>
                        <td><span class="badge">{{ $incident->severity ?? 'Unrated' }}</span></td>
                        <td><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span></td>
                        <td><a href="{{ route('dispatcher.incidents.show', $incident) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No active incidents have been reported.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
