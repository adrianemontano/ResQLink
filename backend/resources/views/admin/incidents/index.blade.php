@extends('layouts.dashboard', ['title' => 'Incident Records | ResQLink'])

@php
    $recordScopes = [
        'all' => 'All Records',
        'active' => 'Active',
        'archived' => 'Archived / Historical',
    ];
    $currentScope = $filters['scope'] ?? 'all';
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Records</h1>
            <p>Complete historical log of all reported incidents. Records are read-only.</p>
        </div>
    </div>

    <form class="card record-filter-row" method="GET" action="{{ route('admin.incidents.index') }}">
        @if ($currentScope !== 'all')
            <input type="hidden" name="scope" value="{{ $currentScope }}">
        @endif

        <label for="incident-search" class="sr-only">Search incidents</label>
        <input id="incident-search" name="search" value="{{ $filters['search'] ?? '' }}"
            placeholder="ID, reporter, category, location, persons, radius, date, notes, status, or severity">

        <label for="incident-category" class="sr-only">Category</label>
        <select id="incident-category" name="category">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>

        <label for="incident-status" class="sr-only">Status</label>
        <select id="incident-status" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>

        <button type="submit">Filter</button>
        <a class="button secondary" href="{{ route('admin.incidents.index') }}">Clear</a>
    </form>

    <section class="card" aria-label="Incident search results">
        <nav class="record-scope-nav" aria-label="Record scope">
            @foreach ($recordScopes as $scope => $label)
                <a
                    class="record-scope-item {{ $currentScope === $scope ? 'active' : '' }}"
                    href="{{ route('admin.incidents.index', array_filter(['search' => $filters['search'] ?? null, 'category' => $filters['category'] ?? null, 'status' => $filters['status'] ?? null, 'scope' => $scope === 'all' ? null : $scope])) }}"
                >{{ $label }} <span class="record-scope-count">{{ $recordCounts[$scope] }}</span></a>
            @endforeach
        </nav>

        <p class="record-summary">
            Showing {{ $incidents->firstItem() ?? 0 }}–{{ $incidents->lastItem() ?? 0 }}
            of {{ $incidents->total() }} incidents
        </p>

        <table>
            <thead>
                <tr>
                    <th scope="col">Incident ID</th>
                    <th scope="col">Reporter</th>
                    <th scope="col">Category</th>
                    <th scope="col">Persons</th>
                    <th scope="col">Barangay</th>
                    <th scope="col">Landmark</th>
                    <th scope="col">Reported</th>
                    <th scope="col">Status</th>
                    <th scope="col">Severity</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? $incident->volunteer?->name ?? '—' }}</td>
                        <td>{{ $incident->category ?? '—' }}</td>
                        <td>{{ $incident->affected_population ?? $incident->persons_count ?? '—' }}</td>
                        <td>{{ $incident->barangay ?? '—' }}</td>
                        <td>{{ $incident->nearest_landmark ?? $incident->landmark ?? '—' }}</td>
                        <td>{{ ($incident->reported_at ?? $incident->created_at)?->timezone(config('app.timezone'))->format('M d, Y H:i') ?? '—' }}</td>
                        <td>
                            <span class="badge status-{{ strtolower($incident->status ?? 'unknown') }}">{{ $incident->status ?? '—' }}</span>
                        </td>
                        <td><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? '—' }}</span></td>
                        <td><a href="{{ route('admin.incidents.show', $incident) }}">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            {{ $currentScope === 'archived' ? 'No archived incidents found.' : 'No matching incidents found.' }}
                            Try changing or clearing the filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <nav class="records-pagination" aria-label="Incident records pages">
            {{ $incidents->links() }}
        </nav>
    </section>
@endsection
