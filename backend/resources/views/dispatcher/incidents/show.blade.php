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

    <section class="detail-grid">
        <div class="detail-left">
            <section class="card compact">
                <h2 style="margin-top: 0">Update Status</h2>
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
                    @error('status')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                    <label>History note
                        <textarea name="notes" rows="3" maxlength="5000" placeholder="Optional dispatch note">{{ old('notes') }}</textarea>
                    </label>
                    <button type="submit">Save status</button>
                </form>

                <h2 style="margin-top: 24px">Incident Summary</h2>
                <dl class="detail-list">
                    <dt>Reporter</dt><dd>{{ $incident->reporter?->name ?? 'Unknown' }}</dd>
                    <dt>Category</dt><dd>{{ $incident->category }}</dd>
                    <dt>Affected persons</dt><dd>{{ $incident->affected_population ?? $incident->persons_count }}</dd>
                    <dt>Barangay</dt><dd>{{ $incident->barangay }}</dd>
                    <dt>Nearest landmark</dt><dd>{{ $incident->nearest_landmark ?? $incident->landmark ?? 'Not provided' }}</dd>
                    <dt>Impact radius</dt><dd>{{ $incident->impact_radius ? $incident->impact_radius.' metres' : 'Not provided' }}</dd>
                    <dt>Coordinates</dt><dd>{{ $incident->latitude }}, {{ $incident->longitude }}</dd>
                    <dt>Preliminary severity</dt><dd><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></dd>
                    <dt>Notes</dt><dd>{{ $incident->notes ?? 'No notes provided.' }}</dd>
                </dl>

                <h2 style="margin-top: 24px">Status History</h2>
                <table style="margin-top: 0">
                    <thead><tr><th>Status</th><th>Dispatcher</th><th>Changed</th><th>Notes</th></tr></thead>
                    <tbody>
                        @forelse ($incident->history as $entry)
                            <tr><td><span class="badge status-{{ strtolower($entry->status?->name ?? 'unknown') }}">{{ $entry->status?->name ?? 'Unknown' }}</span></td><td>Dispatcher</td><td>{{ $entry->changed_at->format('M j, Y g:i A') }}</td><td>{{ $entry->notes ?? '—' }}</td></tr>
                        @empty
                            <tr><td colspan="4">No status changes recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <div class="detail-right">
            <section class="card compact map-card">
                <h2 style="margin-top: 0">Incident Location</h2>
                @if ($incident->latitude !== null && $incident->longitude !== null)
                    <div class="map-wrap">
                        <x-incident-map map-id="incident-mini-map" :focus-lat="$incident->latitude" :focus-lng="$incident->longitude" :focus-incident-id="$incident->id" />
                    </div>
                @else
                    <p style="color: var(--gray-500); font-size: 13px; padding: var(--space-4); text-align: center;">No location data available.</p>
                @endif
            </section>
        </div>
    </section>
@endsection

@if ($incident->latitude !== null && $incident->longitude !== null)
    @push('scripts')
    @endpush
@endif
