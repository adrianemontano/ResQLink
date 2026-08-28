@extends('layouts.dashboard', ['title' => 'Incident Details | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <a href="{{ route('dispatcher.incidents.index') }}">Back to incidents</a>
            <h1>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p>{{ $incident->category }} incident reported {{ $incident->reported_at?->format('M j, Y g:i A') }}</p>
        </div>
        <span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span>
    </div>

    <div class="detail-grid">
        <section class="card">
            <h2>Incident Summary</h2>
            <dl class="detail-list">
                <dt>Reporter</dt><dd>{{ $incident->reporter?->name ?? 'Unknown' }}</dd>
                <dt>Category</dt><dd>{{ $incident->category }}</dd>
                <dt>Affected persons</dt><dd>{{ $incident->affected_population ?? $incident->persons_count }}</dd>
                <dt>Barangay</dt><dd>{{ $incident->barangay }}</dd>
                <dt>Nearest landmark</dt><dd>{{ $incident->nearest_landmark ?? $incident->landmark ?? 'Not provided' }}</dd>
                <dt>Impact radius</dt><dd>{{ $incident->impact_radius ? $incident->impact_radius.' metres' : 'Not provided' }}</dd>
                <dt>Coordinates</dt><dd>{{ $incident->latitude }}, {{ $incident->longitude }}</dd>
                <dt>Preliminary severity</dt><dd>{{ $incident->severity ?? 'Unrated' }}</dd>
                <dt>Notes</dt><dd>{{ $incident->notes ?? 'No notes provided.' }}</dd>
            </dl>
            @if ($incident->latitude !== null && $incident->longitude !== null)
                <h3>Incident Location</h3>
                <x-incident-map map-id="incident-mini-map" />
            @endif
        </section>

        <section class="card">
            <h2>Update Status</h2>
            <form method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}">
                @csrf
                @method('PATCH')
                <label>Status
                    <select name="status" required>
                        @foreach (['Reported', 'Received', 'Dispatched', 'Completed'] as $status)
                            <option value="{{ $status }}" @selected($incident->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label>History note
                    <textarea name="notes" rows="4" maxlength="5000" placeholder="Optional dispatch note"></textarea>
                </label>
                <button type="submit">Save status</button>
            </form>
        </section>
    </div>

    <section class="card">
        <h2>Status History</h2>
        <table>
            <thead><tr><th>Status</th><th>Dispatcher</th><th>Changed</th><th>Notes</th></tr></thead>
            <tbody>
                @forelse ($incident->history as $entry)
                    <tr><td>{{ $entry->status?->name ?? 'Unknown' }}</td><td>{{ $entry->dispatcher?->name ?? 'Unknown' }}</td><td>{{ $entry->changed_at->format('M j, Y g:i A') }}</td><td>{{ $entry->notes ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="4">No status changes recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

@if ($incident->latitude !== null && $incident->longitude !== null)
    @push('scripts')
    @endpush
@endif