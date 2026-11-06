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
            <a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.index') }}">View all incidents</a>
        </div>
        <nav class="status-filter-chips" aria-label="Filter incidents by status">
            @foreach ($statusFilters as $filter)
                <a
                    class="status-filter-chip status-filter-chip-{{ $filter['slug'] }}{{ $selectedStatus === $filter['slug'] ? ' active' : '' }}"
                    href="{{ route('dispatcher.dashboard', ['status' => $filter['slug'], 'per_page' => $perPage]) }}"
                    @if ($selectedStatus === $filter['slug']) aria-current="page" @endif
                >
                    {{ $filter['label'] }}
                    <span class="status-filter-count">{{ $filter['count'] }}</span>
                </a>
            @endforeach
        </nav>
        <table>
            <thead>
                <tr><th>Incident</th><th>Reported by</th><th>Reported at</th><th>Category</th><th>Persons</th><th>Barangay</th><th>Severity</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? 'Unknown' }}
                            @if ($incident->reporter?->volunteerProfile?->barangay || $incident->reporter?->contact_number)
                                <br><small style="color:var(--text-muted)">{{ $incident->reporter?->volunteerProfile?->barangay ?? $incident->reporter?->contact_number }}</small>
                            @endif
                        </td>
                        <td>{{ ($incident->reported_at ?? $incident->created_at)->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                        <td>{{ $incident->category }}</td>
                        <td>{{ $incident->affected_population ?? $incident->persons_count }}</td>
                        <td>{{ $incident->barangay }}</td>
                        <td><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></td>
                        <td><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span></td>
                        <td><a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.show', $incident) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9">No incidents with this status.</td></tr>
                @endforelse
            </tbody>
        </table>
        <x-dispatcher-pagination :paginator="$incidents" :per-page="$perPage" />
    </section>
@endsection
