<div id="dispatch-modal" class="status-modal" role="presentation" hidden>
    <div class="status-modal-backdrop" data-modal-dismiss></div>
    <section class="dispatch-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="dispatch-modal-title" style="max-width: 700px; width: 92%;">
        <header class="dispatch-modal-header">
            <h2 id="dispatch-modal-title">Dispatch response units</h2>
            <p id="dispatch-modal-incident">Incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }} — {{ $incident->category }} ({{ $incident->barangay }})</p>
        </header>

        <form id="dispatch-form" method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}" novalidate>
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="Dispatched">
            <input type="hidden" name="changed_at" id="dispatch-changed-at" value="{{ now()->timezone('Asia/Manila')->format('Y-m-d\TH:i') }}">

            <div class="dispatch-modal-body">
                {{-- Drop search section --}}
                <div class="dispatch-dropsearch-section">
                    <label for="dispatch-team-search" class="dispatch-field-label">Search &amp; select team(s)</label>
                    <div class="dropsearch-input-wrap">
                        <input
                            type="text"
                            id="dispatch-team-search"
                            class="dispatch-search-input"
                            placeholder="Type to search team by name or type..."
                            autocomplete="off"
                        >
                        {{-- Scrollable dropdown: ~5 items visible, scrollable --}}
                        <div class="dispatch-team-dropdown" id="dispatch-team-dropdown" hidden>
                            <div class="dropdown-team-list" id="dropdown-team-list">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Selected teams list with X delete icon --}}
                <div class="selected-teams-section" id="selected-teams-section" hidden>
                    <div class="selected-teams-header">
                        <span class="selected-teams-label">Selected teams (<span id="selected-count">0</span>)</span>
                    </div>
                    <div class="selected-teams-list" id="selected-teams-chips">
                        {{-- Selected team items with X button will be rendered here --}}
                    </div>
                    <div id="hidden-team-inputs"></div>
                </div>

                {{-- External team section --}}
                <div class="external-team-block">
                    <button type="button" class="btn-toggle-external" id="btn-toggle-external">+ Add external team (not in list)</button>
                    <div class="external-team-inputs-row" id="external-team-row" hidden>
                        <input type="text" id="external-name-input" class="dispatch-search-input" placeholder="External team name" maxlength="100">
                        <input type="text" id="external-contact-input" class="dispatch-search-input" placeholder="Contact number (optional)" maxlength="50" style="max-width: 220px;">
                        <button type="button" class="button secondary dispatcher-view-button" id="btn-add-external-confirm">Add</button>
                    </div>
                </div>

                {{-- Dispatch note --}}
                <div class="dispatch-notes-section">
                    <label for="dispatch-notes" class="dispatch-field-label">Dispatch note <span class="required-asterisk">*</span></label>
                    <textarea id="dispatch-notes" name="notes" rows="3" maxlength="300" required placeholder="Add instructions or notes for this dispatch...">{{ old('notes') }}</textarea>
                    <div class="dispatch-note-counter" id="dispatch-note-counter">0 / 300 characters</div>
                    <div id="dispatch-error" class="status-modal-error" role="alert" hidden></div>
                </div>
            </div>

            <footer class="dispatch-modal-footer">
                <button type="button" class="button secondary dispatcher-view-button" data-modal-dismiss>Cancel</button>
                <button type="submit" class="button primary" id="btn-submit-dispatch" disabled>Confirm Dispatch</button>
            </footer>
        </form>
    </section>
</div>
