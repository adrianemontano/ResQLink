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
                <td><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span></td>
                <td><a href="{{ route('dispatcher.incidents.show', $incident) }}">Open</a></td>
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
