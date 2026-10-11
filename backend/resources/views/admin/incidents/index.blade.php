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

    <form class="card" method="GET">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search ID, reporter, category, barangay, landmark">
        <select name="category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select>
        <select name="status"><option value="">All statuses</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button type="submit">Filter</button>
        <a class="button secondary" href="{{ route('admin.incidents.index') }}">Clear</a>
    </form>

    <section class="card">
        <nav class="record-scope-nav" aria-label="Record scope">
            @foreach ($recordScopes as $scope => $label)
                <a
                    class="record-scope-item {{ $currentScope === $scope ? 'active' : '' }}"
                    href="{{ route('admin.incidents.index', array_filter(['search' => $filters['search'] ?? null, 'category' => $filters['category'] ?? null, 'status' => $filters['status'] ?? null, 'scope' => $scope === 'all' ? null : $scope])) }}"
                >{{ $label }} <span class="record-scope-count">{{ $recordCounts[$scope] }}</span></a>
            @endforeach
        </nav>

        <table>
            <thead>
                <tr>
                    <th>Incident ID</th>
                    <th>Reporter</th>
                    <th>Category</th>
                    <th>Persons</th>
                    <th>Barangay</th>
                    <th>Landmark</th>
                    <th>Reported</th>
                    <th>Status</th>
                    <th>Severity</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? '—' }}</td>
                        <td>{{ $incident->category }}</td>
                        <td>{{ $incident->affected_population ?? $incident->persons_count }}</td>
                        <td>{{ $incident->barangay }}</td>
                        <td>{{ $incident->nearest_landmark ?? $incident->landmark ?? '—' }}</td>
                        <td>{{ ($incident->reported_at ?? $incident->created_at)?->format('M d, Y H:i') }}</td>
                        <td>
                            <span class="badge status-{{ strtolower($incident->status) }}">{{ ucfirst($incident->status) }}</span>
                        </td>
                        <td>{{ $incident->severity ?? '—' }}</td>
                        <td><a href="{{ route('admin.incidents.show', $incident) }}">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">{{ $currentScope === 'archived' ? 'No archived incidents found.' : 'No matching incidents found.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $incidents->links() }}
        </div>
    </section>
@endsection
