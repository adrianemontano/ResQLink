@extends('layouts.dashboard', ['title' => 'Incident Records | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Records</h1>
            <p>Read-only access to all persisted incidents, including completed and historical records.</p>
        </div>
    </div>

    <form class="card" method="GET" action="{{ route('admin.incidents.index') }}">
        <label for="incident-search">Search incidents</label>
        <input id="incident-search" name="search" value="{{ $filters['search'] ?? '' }}"
            placeholder="ID, reporter, category, location, persons, radius, date, notes, status, or severity">

        <label for="incident-category">Category</label>
        <select id="incident-category" name="category">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
            @endforeach
        </select>

        <label for="incident-status">Status</label>
        <select id="incident-status" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>

        <button type="submit">Apply filters</button>
        <a class="button secondary" href="{{ route('admin.incidents.index') }}">Clear filters</a>
    </form>

    <section class="card" aria-label="Incident search results">
        <p>
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
                        <td>{{ $incident->status ?? '—' }}</td>
                        <td>{{ $incident->severity ?? '—' }}</td>
                        <td><a href="{{ route('admin.incidents.show', $incident) }}">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">No matching incidents found. Try changing or clearing the filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <nav aria-label="Incident records pages" style="margin-top: 1rem;">
            {{ $incidents->links() }}
        </nav>
    </section>
@endsection
