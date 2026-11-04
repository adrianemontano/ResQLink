<table>
    <thead>
        <tr>
            <th>Incident ID</th>
            <th>Reported by</th>
            <th>Category</th>
            <th>Persons</th>
            <th>Barangay</th>
            <th>Reported at</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($incidents as $incident)
            <tr>
                <td>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <strong>{{ $incident->reporter?->name ?? '—' }}</strong>
                    @if ($incident->reporter?->volunteerProfile?->barangay || $incident->reporter?->contact_number)
                        <br><small style="color: var(--text-muted);">{{ $incident->reporter?->volunteerProfile?->barangay ? 'Barangay: '.$incident->reporter->volunteerProfile->barangay : '' }}{{ $incident->reporter?->volunteerProfile?->barangay && $incident->reporter?->contact_number ? ' · ' : '' }}{{ $incident->reporter?->contact_number ? 'Contact: '.$incident->reporter->contact_number : '' }}</small>
                    @endif
                </td>
                <td>{{ $incident->category }}</td>
                <td>{{ $incident->persons_count }}</td>
                <td>{{ $incident->barangay }}</td>
                <td>{{ ($incident->reported_at ?? $incident->created_at)->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                <td><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></td>
                <td><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span></td>
                <td><a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.show', $incident) }}">View</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No incidents have been reported yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="incident-pagination">
    {{ $incidents->links() }}
</div>
