@extends('layouts.dashboard', ['title' => 'Cancel Dispatch | ResQLink'])

@section('content')
    <a class="incident-back-link" href="{{ route('dispatcher.incidents.show', $incident) }}">← Back to incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</a>

    @php
        $activeTeams = $incident->responseTeams()->wherePivot('status', 'active')->get()->sortByDesc('pivot.dispatched_at');
    @endphp

    <section class="card" style="max-width: 760px; margin: 0 auto;">
        <header class="cancel-modal-header">
            <h1 style="font-size: 20px; margin: 0;">Cancel dispatch — INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p>Select one or more active teams to cancel.</p>
        </header>

        <form id="cancel-dispatch-form" method="POST" action="{{ route('dispatcher.incidents.cancel-dispatch', $incident) }}" novalidate>
            @csrf
            <div class="cancel-modal-body" style="max-height: none; overflow: visible;">
                <div class="cancel-search-section" style="margin-bottom: 12px;">
                    <input
                        type="text"
                        id="cancel-team-search"
                        class="dispatch-search-input"
                        placeholder="Search active team name..."
                        autocomplete="off"
                    >
                </div>

                @if ($activeTeams->isEmpty())
                    <div class="manage-empty">No active teams to cancel.</div>
                @else
                    <div class="cancel-team-list" id="cancel-team-list">
                        @foreach ($activeTeams as $team)
                            <label class="manage-team-row" data-team-name="{{ strtolower($team->name) }}">
                                <span class="manage-checkbox">
                                    <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" class="cancel-team-checkbox">
                                </span>
                                <span class="manage-team-info">
                                    <span class="manage-team-name">{{ $team->name }}</span>
                                    <span class="manage-team-type">{{ $team->type }}</span>
                                </span>
                                <span class="manage-team-time">Dispatched {{ $team->pivot->dispatched_at ? \Carbon\Carbon::parse($team->pivot->dispatched_at)->timezone('Asia/Manila')->format('M d, h:i A') : 'N/A' }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="cancel-notes-section" style="margin-top: 16px;">
                    <label for="cancel-reason-notes">Cancellation reason <span class="required-asterisk" aria-hidden="true">*</span></label>
                    <textarea
                        id="cancel-reason-notes"
                        name="notes"
                        rows="3"
                        maxlength="300"
                        required
                        placeholder="Explain why these teams are being cancelled..."
                    >{{ old('notes') }}</textarea>
                    @error('notes')<div class="status-modal-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="status-modal-actions">
                <a href="{{ route('dispatcher.incidents.show', $incident) }}" class="btn-secondary-modal" style="text-decoration: none; display: inline-flex; align-items: center;">Back</a>
                <button type="submit" class="btn-danger-modal" @if ($activeTeams->isEmpty()) disabled @endif>Confirm Cancellation</button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('cancel-team-search');
            const rows = document.querySelectorAll('#cancel-team-list .manage-team-row');
            if (search) {
                search.addEventListener('input', () => {
                    const q = search.value.trim().toLowerCase();
                    rows.forEach((row) => {
                        const name = row.getAttribute('data-team-name') || '';
                        row.hidden = q !== '' && !name.includes(q);
                    });
                });
            }
        });
    </script>
@endpush

@push('styles')
    @include('dispatcher.incidents.partials.status-styles')
@endpush
