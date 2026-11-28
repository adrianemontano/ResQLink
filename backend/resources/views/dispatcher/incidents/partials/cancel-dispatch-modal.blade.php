<div id="cancel-dispatch-modal" class="status-modal" role="presentation" hidden>
    <div class="status-modal-backdrop" data-modal-dismiss></div>
    <section class="cancel-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title" style="max-width: 600px; width: 92%;">
        <header class="cancel-modal-header">
            <h2 id="cancel-modal-title" style="color: var(--danger, #D92200);">Cancel dispatch</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 13px;">Incident INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }} — select the team(s) to cancel</p>
        </header>

        <form id="cancel-dispatch-form" method="POST" action="{{ route('dispatcher.incidents.cancel-dispatch', $incident) }}" novalidate>
            @csrf
            <div class="cancel-modal-body" style="padding: 1.25rem 1.5rem;">
                @php
                    $activeDispatchedTeams = $incident->responseTeams()->wherePivot('status', 'active')->get();
                @endphp

                <label class="dispatch-field-label">Dispatched teams</label>
                @if ($activeDispatchedTeams->isEmpty())
                    <div class="empty-notice" style="padding: 1rem; background: var(--surface-alt, #F5F0EB); border-radius: 6px; font-size: 13px; color: var(--text-muted); text-align: center;">
                        No active dispatched teams for this incident.
                    </div>
                @else
                    <div class="cancel-team-list" id="cancel-team-list" style="max-height: 220px; overflow-y: auto; margin-bottom: 1rem; border: 1px solid var(--border, #EDE2D8); border-radius: 8px;">
                        @foreach ($activeDispatchedTeams as $team)
                            <label class="manage-team-row" style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-bottom: 1px solid var(--border, #EDE2D8); cursor: pointer;">
                                <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" class="cancel-team-checkbox" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--danger, #D92200);">
                                <div class="manage-team-info" style="flex: 1;">
                                    <div class="manage-team-name" style="font-weight: 600; font-size: 14px; color: var(--text-primary);">{{ $team->name }}</div>
                                    <div class="manage-team-type" style="font-size: 12px; color: var(--text-muted);">{{ $team->type }}</div>
                                </div>
                                <span class="manage-team-time" style="font-size: 12px; color: var(--text-muted);">
                                    Dispatched {{ $team->pivot->dispatched_at ? \Carbon\Carbon::parse($team->pivot->dispatched_at)->timezone('Asia/Manila')->format('M d, h:i A') : 'N/A' }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="cancel-warning" style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 10px 12px; font-size: 12px; color: #92400E; margin-bottom: 1rem;">
                    Cancelled teams will be released from this mission and become available for future dispatches.
                </div>

                <div class="cancel-note-section">
                    <label for="cancel-notes" class="dispatch-field-label">Cancellation reason <span class="required-asterisk">*</span></label>
                    <textarea
                        id="cancel-notes"
                        name="cancel_reason"
                        rows="3"
                        maxlength="300"
                        required
                        class="status-modal-textarea"
                        style="width: 100%; padding: 10px; border: 1px solid var(--border, #EDE2D8); border-radius: 6px; font: inherit; font-size: 14px;"
                        placeholder="State why you are cancelling this team dispatch..."
                    ></textarea>
                    <div class="status-note-counter" id="cancel-note-counter" style="text-align: right; font-size: 11px; color: var(--text-muted); margin-top: 4px;">0 / 300 characters</div>
                    <div class="status-modal-error" id="cancel-modal-error" hidden style="color: var(--danger, #D92200); font-size: 12px; margin-top: 4px;"></div>
                </div>
            </div>

            <footer class="cancel-modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border, #EDE2D8); display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="button secondary dispatcher-view-button" data-modal-dismiss>Cancel</button>
                <button type="submit" id="btn-submit-cancel" class="button danger" @if ($activeDispatchedTeams->isEmpty()) disabled @endif>Confirm Cancellation</button>
            </footer>
        </form>
    </section>
</div>
