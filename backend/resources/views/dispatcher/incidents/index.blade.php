@extends('layouts.dashboard', ['title' => 'Incidents | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Management</h1>
            <p>Live queue of emergencies reported by volunteers.</p>
        </div>
    </div>

    <form class="card filter-row" method="GET" action="{{ route('dispatcher.incidents.index') }}">
        <label>Category
            <select name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label>Barangay
            <input type="search" name="barangay" value="{{ request('barangay') }}" placeholder="Search barangay">
        </label>
        <button type="submit">Filter</button>
        <a href="{{ route('dispatcher.incidents.index') }}">Clear</a>
    </form>

    <section class="card">
        <table>
            <thead>
                <tr>
                    <th>Incident ID</th>
                    <th>Reporter</th>
                    <th>Category</th>
                    <th>Persons</th>
                    <th>Barangay</th>
                    <th>Reported</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($incidents as $incident)
                    <tr>
                        <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $incident->reporter?->name ?? '—' }}</td>
                        <td>{{ $incident->category }}</td>
                        <td>{{ $incident->persons_count }}</td>
                        <td>{{ $incident->barangay }}</td>
                        <td>{{ $incident->reported_at?->diffForHumans() ?? $incident->created_at->diffForHumans() }}</td>
                        <td><span class="badge">{{ $incident->severity ?? 'Unrated' }}</span></td>
                        <td><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span>
                        </td>
                        <td><a href="{{ route('dispatcher.incidents.show', $incident) }}">Open</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No incidents have been reported yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $incidents->links() }}
        </div>
    </section>
@endsection
