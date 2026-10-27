@extends('layouts.dashboard', ['title' => 'Incident Record | ResQLink'])

@php
    use App\Models\Incident;
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p>Read-only administrative incident record. Historical and completed records remain available here.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.incidents.index') }}">Back to records</a>
    </div>

    <section class="card" aria-label="Incident details">
        <p><strong>Record state:</strong>
            <span class="badge status-{{ strtolower($incident->status ?? 'unknown') }}">
                {{ $incident->status === Incident::STATUS_COMPLETED ? 'Archived / Historical' : 'Active' }}
            </span>
        </p>
        <p><strong>Reporter:</strong> {{ $incident->reporter?->name ?? $incident->volunteer?->name ?? '—' }}</p>
        <p><strong>Category:</strong> {{ $incident->category ?? '—' }}</p>
        <p><strong>Barangay:</strong> {{ $incident->barangay ?? '—' }}</p>
        <p><strong>Nearest landmark:</strong> {{ $incident->nearest_landmark ?? $incident->landmark ?? '—' }}</p>
        <p><strong>Affected persons:</strong> {{ $incident->affected_population ?? $incident->persons_count ?? '—' }}</p>
        <p><strong>Location:</strong> {{ $incident->latitude ?? '—' }}, {{ $incident->longitude ?? '—' }}</p>
        <p><strong>Impact radius:</strong> {{ $incident->impact_radius ?? '—' }} metres</p>
        <p><strong>Preliminary severity:</strong>
            <span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? '—' }}</span>
        </p>
        <p><strong>Current status:</strong>
            <span class="badge status-{{ strtolower($incident->status ?? 'unknown') }}">{{ $incident->status ?? '—' }}</span>
        </p>
        <p><strong>Reported:</strong> {{ ($incident->reported_at ?? $incident->created_at)?->timezone(config('app.timezone'))->format('M d, Y H:i') ?? '—' }} {{ config('app.timezone') }}</p>
        <p><strong>Notes:</strong> {{ $incident->notes ?: '—' }}</p>
    </section>

    <section class="card" aria-label="Incident status history">
        <h2>Status history</h2>
        @forelse ($incident->history as $history)
            <article>
                <p>
                    {{ $history->changed_at?->timezone(config('app.timezone'))->format('M d, Y H:i') ?? '—' }}
                    {{ config('app.timezone') }} — {{ $history->status?->name ?? 'Status updated' }}
                    by {{ $history->dispatcher?->name ?? 'Unknown user' }}
                </p>
                @if ($history->notes)
                    <p>{{ $history->notes }}</p>
                @endif
            </article>
        @empty
            <p>No status history recorded for this incident.</p>
        @endforelse
    </section>
@endsection
