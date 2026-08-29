@extends('layouts.dashboard', ['title' => 'Incident Record | ResQLink'])

@section('content')
    <div class="header-row"><div><h1>Incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h1><p>Read-only administrative incident record.</p></div><a class="button secondary" href="{{ route('admin.incidents.index') }}">Back</a></div>
    <section class="card">
        <p><strong>Reporter:</strong> {{ $incident->reporter?->name ?? '—' }}</p>
        <p><strong>Category:</strong> {{ $incident->category ?? '—' }}</p>
        <p><strong>Barangay:</strong> {{ $incident->barangay ?? '—' }}</p>
        <p><strong>Landmark:</strong> {{ $incident->landmark ?? $incident->nearest_landmark ?? '—' }}</p>
        <p><strong>Affected persons:</strong> {{ $incident->persons_count ?? $incident->affected_population ?? 0 }}</p>
        <p><strong>Location:</strong> {{ $incident->latitude ?? '—' }}, {{ $incident->longitude ?? '—' }}; radius {{ $incident->impact_radius ?? '—' }}</p>
        <p><strong>Severity:</strong> {{ $incident->severity ?? '—' }} | <strong>Status:</strong> {{ ucfirst($incident->status ?? '—') }}</p>
        <p><strong>Reported:</strong> {{ ($incident->reported_at ?? $incident->created_at)?->format('M d, Y H:i') }}</p>
        <p><strong>Notes:</strong> {{ $incident->notes ?? '—' }}</p>
    </section>
    <section class="card"><h2>Status History</h2>
        @forelse ($incident->history as $history)
            <p>{{ $history->changed_at?->format('M d, Y H:i') }} — {{ $history->status?->name ?? 'Updated' }} by {{ $history->dispatcher?->name ?? 'System' }}</p>
        @empty <p>No status history recorded.</p> @endforelse
    </section>
@endsection
