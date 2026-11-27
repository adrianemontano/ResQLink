<div class="table-scroll">
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
            <th>Teams</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($incidents as $incident)
            <tr>
                <td data-label="Incident ID">INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td data-label="Reported by">
                    <strong>{{ $incident->reporter?->name ?? '—' }}</strong>
                    @if ($incident->reporter?->volunteerProfile?->barangay || $incident->reporter?->contact_number)
                        <small class="dispatch-subline">{{ $incident->reporter?->volunteerProfile?->barangay ? 'Barangay: '.$incident->reporter->volunteerProfile->barangay : '' }}{{ $incident->reporter?->volunteerProfile?->barangay && $incident->reporter?->contact_number ? ' · ' : '' }}{{ $incident->reporter?->contact_number ? 'Contact: '.$incident->reporter->contact_number : '' }}</small>
                    @endif
                </td>
                <td data-label="Category">{{ $incident->category }}</td>
                <td data-label="Persons">{{ $incident->persons_count }}</td>
                <td data-label="Barangay">{{ $incident->barangay }}</td>
                <td data-label="Reported at">{{ ($incident->reported_at ?? $incident->created_at)->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                <td data-label="Severity"><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></td>
                <td data-label="Status"><span class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span></td>
                <td data-label="Teams">{{ $incident->dispatches->filter->isActive()->map(fn ($dispatch) => $dispatch->team?->name)->filter()->implode(', ') ?: '—' }}</td>
                <td data-label="Action"><a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.show', $incident) }}">View</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="10">No incidents have been reported yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

<x-dispatcher-pagination :paginator="$incidents" :per-page="$perPage" />
