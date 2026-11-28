@extends('layouts.dashboard', ['title' => 'Dispatch Response Units | ResQLink'])

@section('content')
    <a class="incident-back-link" href="{{ route('dispatcher.incidents.show', $incident) }}">← Back to incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</a>

    <section class="card" id="dispatch-modal" style="max-width: 760px; margin: 0 auto;">
        <header class="dispatch-modal-header">
            <h1 id="dispatch-modal-title" style="font-size: 20px; margin: 0;">Dispatch response units</h1>
            <p id="dispatch-modal-incident">INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }} — {{ $incident->category }} ({{ $incident->barangay }})</p>
            @if ($incident->status === 'Received')
                <div class="status-transition" aria-label="Status transition">
                    <span class="badge status-received">Received</span>
                    <span aria-hidden="true">→</span>
                    <span class="badge status-dispatched">Dispatched</span>
                </div>
            @endif
        </header>

        <form id="dispatch-form" method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}" novalidate>
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="Dispatched">
            <input type="hidden" name="changed_at" id="dispatch-changed-at" value="{{ now()->timezone('Asia/Manila')->format('Y-m-d\TH:i') }}">

            <div class="dispatch-modal-body" style="max-height: none; overflow: visible;">
                <div class="dispatch-search-section">
                    <input
                        type="text"
                        id="dispatch-team-search"
                        class="dispatch-search-input"
                        placeholder="Search team name or type..."
                        autocomplete="off"
                    >
                    <div class="dispatch-type-filters" id="dispatch-type-filters">
                        <button type="button" class="type-filter-chip active" data-type="all">All</button>
                    </div>
                    <div class="dispatch-options">
                        <label class="available-toggle">
                            <input type="checkbox" id="available-only" checked>
                            <span>Available only</span>
                        </label>
                        <button type="button" class="show-busy-link" id="show-busy-link" hidden>Show busy (0)</button>
                    </div>
                    <div class="dispatch-result-count" id="dispatch-result-count" aria-live="polite">Showing 0 of 0 teams</div>
                </div>

                <div class="selected-teams-section" id="selected-teams-section" hidden>
                    <div class="selected-teams-label">Selected (<span id="selected-count">0</span>)</div>
                    <div class="selected-teams-chips" id="selected-teams-chips"></div>
                </div>

                <div class="dispatch-team-list" id="dispatch-team-list">
                    <div class="team-list-empty" id="team-list-empty">No teams found</div>
                </div>

                <div class="dispatch-select-all">
                    <button type="button" id="select-all-shown" disabled>Select all 0 shown</button>
                </div>

                <div class="external-team-section">
                    <button type="button" class="add-external-team-link" id="add-external-team-link">+ Add external team</button>
                    <div class="external-team-form" id="external-team-form" hidden>
                        <div class="external-team-inputs">
                            <input type="text" name="external_team_name[]" class="status-modal-select external-team-name" placeholder="External team name" maxlength="100">
                            <input type="text" name="external_team_contact[]" class="status-modal-select external-team-contact" placeholder="Contact (optional)" maxlength="50">
                            <button type="button" class="btn-remove-external" aria-label="Remove external team">×</button>
                        </div>
                        <button type="button" class="add-another-external" id="add-another-external">+ Add another</button>
                    </div>
                </div>

                <div class="dispatch-notes-section">
                    <label for="dispatch-notes">Dispatch note <span class="required-asterisk" aria-hidden="true">*</span></label>
                    <textarea id="dispatch-notes" name="notes" rows="3" maxlength="300" required placeholder="Add dispatch instructions or notes">{{ old('notes') }}</textarea>
                    <div class="dispatch-note-counter" id="dispatch-note-counter">0 / 300 characters</div>
                    <div id="dispatch-error" class="status-modal-error" role="alert" hidden></div>
                </div>
            </div>

            <div class="status-modal-actions">
                <a href="{{ route('dispatcher.incidents.show', $incident) }}" class="btn-secondary-modal" style="text-decoration: none; display: inline-flex; align-items: center;">Cancel</a>
                <button type="submit" class="btn-primary-modal" id="confirm-dispatch-btn">Confirm Dispatch</button>
            </div>
        </form>
    </section>

    <div id="manage-teams-modal" hidden></div>
    <div id="cancel-dispatch-modal" hidden></div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dispatch-modal.js') }}" defer></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dispatcher-status-confirmation.css') }}">
@endpush
