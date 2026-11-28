@extends('layouts.dashboard', ['title' => 'Incident Details | ResQLink'])

@section('content')
    <a class="incident-back-link" href="{{ route('dispatcher.incidents.index') }}">← Back to incidents</a>
    <section class="detail-grid">
        <div class="detail-left">
            <section class="card compact incident-details-card">
                <div class="incident-identity">
                    <div>
                        <h1>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h1>
                        <p>{{ $incident->category }} emergency reported {{ ($incident->reported_at ?? $incident->created_at)->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</p>
                    </div>
                    <span id="incident-current-status" class="badge status-{{ strtolower($incident->status) }}" tabindex="-1">{{ $incident->status }}</span>
                </div>

                <div id="completed-incident-banner" class="completed-incident-banner" @if ($incident->status !== 'Completed') hidden @endif>
                    <strong>Incident completed.</strong> This incident can no longer be edited or dispatched.
                </div>

                @if ($incident->status !== 'Completed')
                <h2 class="incident-section-title">Update Status</h2>

                <div id="incident-status-actions" class="incident-status-actions">
                    <div class="status-actions-row">
                        <div class="status-dropdown" id="status-dropdown">
                            <button type="button" class="btn-status-dropdown" id="update-status-toggle" aria-expanded="false" aria-haspopup="menu">
                                <span>Update status</span>
                                <svg class="status-dropdown-caret" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div class="status-dropdown-menu" id="status-dropdown-menu" role="menu" hidden>
                                <button type="button" class="status-dropdown-item" role="menuitem" data-action="status" data-status="Reported">Reported</button>
                                <button type="button" class="status-dropdown-item" role="menuitem" data-action="status" data-status="Received">Received</button>
                                <button type="button" class="status-dropdown-item" role="menuitem" data-action="dispatch">Dispatched (Re-dispatch team)</button>
                                <button type="button" class="status-dropdown-item" role="menuitem" data-action="status" data-status="Completed">Completed</button>
                                <button type="button" class="status-dropdown-item" role="menuitem" data-action="cancel-dispatch">Cancel Dispatch</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-update" id="add-update-btn">
                            + Add update
                        </button>
                    </div>
                </div>
                @endif

                {{-- Response Teams Section --}}
                @if ($incident->status !== 'Reported')
                    @php
                        $allTeams = $incident->responseTeams()->get();
                        $activeTeams = $allTeams->where('pivot.status', 'active');
                        $cancelledTeams = $allTeams->where('pivot.status', 'cancelled');
                        $teamsByType = $activeTeams->groupBy('type')->map->count();
                        $recentTeams = $activeTeams->sortByDesc('pivot.dispatched_at')->take(3);
                    @endphp
                    <h2 class="incident-section-title">Response teams - {{ $activeTeams->count() }} active, {{ $cancelledTeams->count() }} cancelled</h2>
                    <div id="response-teams-section">
                        @if ($allTeams->isEmpty())
                            <p class="no-teams-message">No response teams dispatched.</p>
                        @else
                            {{-- Summary counts by type --}}
                            @if ($activeTeams->isNotEmpty())
                                <div class="teams-summary">
                                    @foreach ($teamsByType as $type => $count)
                                        <span class="team-type-count">{{ $count }} {{ $type }}</span>
                                    @endforeach
                                </div>

                                {{-- Recent active teams --}}
                                <div class="recent-teams-list">
                                    @foreach ($recentTeams as $team)
                                        <div class="recent-team-item">
                                            <span class="team-name">{{ $team->name }}</span>
                                            <span class="team-type">{{ $team->type }}</span>
                                            <span class="team-time">Dispatched {{ $team->pivot->dispatched_at ? \Carbon\Carbon::parse($team->pivot->dispatched_at)->timezone('Asia/Manila')->format('h:i A') : 'N/A' }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($activeTeams->count() > 3)
                                    <button type="button" class="view-all-teams-btn" id="view-all-teams-btn">
                                        View all {{ $activeTeams->count() }} active teams
                                    </button>
                                @endif
                            @endif

                            {{-- Cancelled teams --}}
                            @if ($cancelledTeams->isNotEmpty())
                                <div class="cancelled-teams-section" style="margin-top: 1rem;">
                                    <h3 style="font-size: 14px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px;">Cancelled Teams ({{ $cancelledTeams->count() }})</h3>
                                    <div class="cancelled-teams-list">
                                        @foreach ($cancelledTeams as $team)
                                            <div class="cancelled-team-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: var(--surface-alt); border: 1px solid var(--border); border-radius: 6px; margin-bottom: 6px; opacity: 0.7;">
                                                <span class="team-name" style="font-weight: 600;">{{ $team->name }}</span>
                                                <span class="team-type" style="font-size: 12px; color: var(--text-muted);">{{ $team->type }}</span>
                                                <span class="team-time" style="font-size: 12px; color: var(--text-muted); margin-left: auto;">
                                                    Cancelled {{ $team->pivot->cancelled_at ? \Carbon\Carbon::parse($team->pivot->cancelled_at)->timezone('Asia/Manila')->format('M d, h:i A') : 'N/A' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif

                {{-- Modals --}}
                @include('dispatcher.incidents.partials.dispatch-modal')
                @include('dispatcher.incidents.partials.cancel-dispatch-modal')
                @include('dispatcher.incidents.partials.action-confirmation-modal')

                <div id="completed-incident-modal" class="status-modal" role="presentation" hidden>
                    <div class="status-modal-backdrop" data-completion-dismiss></div>
                    <section class="status-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="completed-modal-title">
                        <h2 id="completed-modal-title">Incident completed</h2>
                        <p>This incident is now complete. Its status, dispatch, and activity notes can no longer be edited.</p>
                        <div class="status-modal-actions">
                            <button type="button" class="button secondary dispatcher-view-button" data-completion-dismiss>Continue viewing</button>
                            <a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.index') }}">Back to incidents</a>
                        </div>
                    </section>
                </div>

                @php
                    $modalStatus = $modalStatus ?? old('status', '');
                    $defaultDateTime = $defaultDateTime ?? now()->format('Y-m-d\TH:i');
                @endphp

                <div id="status-confirmation-modal" class="status-modal" role="presentation" hidden data-open-on-load="{{ $errors->has('status') || $errors->has('notes') || $errors->has('changed_at') ? 'true' : 'false' }}">
                    <div class="status-modal-backdrop" data-modal-dismiss></div>
                    <section class="status-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="status-modal-title" aria-describedby="status-modal-description">
                        <h2 id="status-modal-title">Confirm status update</h2>
                        <p id="status-modal-description">Incident <strong>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</strong></p>

                        <div class="status-transition" aria-label="Status transition">
                            <span id="status-modal-current" class="badge status-{{ strtolower($incident->status) }}">{{ $incident->status }}</span>
                            <span aria-hidden="true">→</span>
                            <span id="status-modal-next" class="badge status-{{ strtolower($modalStatus ?: 'reported') }}">{{ $modalStatus ?: 'Select a status' }}</span>
                        </div>

                        <p id="status-final-warning" class="status-final-warning" @if ($modalStatus !== 'Completed') hidden @endif>
                            Completing this incident is final. It can no longer be edited or dispatched.
                        </p>

                        <form id="incident-status-form" method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}" novalidate>
                            @csrf
                            @method('PATCH')
                            <input id="status-modal-value" type="hidden" name="status" value="{{ $modalStatus }}">

                            <div class="field status-modal-field status-datetime-row">
                                <div>
                                    <label for="status-modal-date">Date <span class="required-asterisk" aria-hidden="true">*</span></label>
                                    <input id="status-modal-date" type="date" name="changed_date" value="{{ old('changed_date', now()->format('Y-m-d')) }}" required class="status-modal-select">
                                </div>
                                <div>
                                    <label for="status-modal-time">Time <span class="required-asterisk" aria-hidden="true">*</span></label>
                                    <input id="status-modal-time" type="time" name="changed_time" value="{{ old('changed_time', now()->format('H:i')) }}" required class="status-modal-select">
                                </div>
                                <input id="status-modal-changed-at" type="hidden" name="changed_at" value="{{ old('changed_at', $defaultDateTime) }}">
                                <div id="status-changed-at-error" class="status-modal-error" role="alert" @if (! $errors->has('changed_at') && ! $errors->has('changed_date') && ! $errors->has('changed_time')) hidden @endif>@error('changed_at'){{ $message }}@enderror @error('changed_date'){{ $message }}@enderror @error('changed_time'){{ $message }}@enderror</div>
                            </div>

                            <label for="status-modal-notes">Required note <span class="required-asterisk" aria-hidden="true">*</span></label>
                            <textarea id="status-modal-notes" name="notes" rows="4" maxlength="300" required aria-describedby="status-note-counter status-note-error" @if ($errors->has('notes')) aria-invalid="true" @endif placeholder="Add a note about this status change">{{ old('notes') }}</textarea>
                            <div id="status-note-counter" class="status-note-counter" aria-live="polite">{{ mb_strlen(old('notes', '')) }} / 300 characters</div>
                            <div id="status-note-error" class="status-modal-error" role="alert" @if (! $errors->has('notes')) hidden @endif>@error('notes'){{ $message }}@enderror</div>
                            <div id="status-modal-error" class="status-modal-error" role="alert" @if (! $errors->has('status')) hidden @endif>@error('status'){{ $message }}@enderror</div>
                            <div class="status-modal-actions">
                                <button type="button" class="button secondary dispatcher-view-button" data-modal-dismiss>Cancel</button>
                                <button id="status-confirm-button" type="submit" class="button secondary dispatcher-view-button status-confirm-button">Confirm</button>
                            </div>
                        </form>
                    </section>
                </div>

                {{-- Add Update Modal --}}
                <div id="add-update-modal" class="status-modal" role="presentation" hidden>
                    <div class="status-modal-backdrop" data-modal-dismiss></div>
                    <section class="status-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="add-update-modal-title" aria-describedby="add-update-modal-description">
                        <h2 id="add-update-modal-title">Add update</h2>
                        <p id="add-update-modal-description">Incident <strong>INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</strong></p>

                        <form id="incident-update-form" method="POST" action="{{ route('dispatcher.incidents.updates.store', $incident) }}" novalidate>
                            @csrf
                            <div class="field status-modal-field status-datetime-row">
                                <div>
                                    <label for="update-modal-date">Date <span class="required-asterisk" aria-hidden="true">*</span></label>
                                    <input id="update-modal-date" type="date" name="changed_date" value="{{ old('changed_date', now()->timezone('Asia/Manila')->format('Y-m-d')) }}" required class="status-modal-select">
                                </div>
                                <div>
                                    <label for="update-modal-time">Time <span class="required-asterisk" aria-hidden="true">*</span></label>
                                    <input id="update-modal-time" type="time" name="changed_time" value="{{ old('changed_time', now()->timezone('Asia/Manila')->format('H:i')) }}" required class="status-modal-select">
                                </div>
                                <input id="update-modal-changed-at" type="hidden" name="changed_at" value="{{ old('changed_at', $defaultDateTime) }}">
                            </div>

                            <div class="field status-modal-field">
                                <label for="update-modal-category">Category <span class="required-asterisk" aria-hidden="true">*</span></label>
                                <select id="update-modal-category" name="category" required class="status-modal-select">
                                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select a category</option>
                                    @foreach (\App\Models\IncidentHistory::UPDATE_CATEGORIES as $category)
                                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="status-modal-error" role="alert">{{ $message }}</div>
                                @enderror
                            </div>

                            <label for="update-modal-notes">Required note <span class="required-asterisk" aria-hidden="true">*</span></label>
                            <textarea id="update-modal-notes" name="notes" rows="4" maxlength="300" required aria-describedby="update-note-counter update-note-error" @if ($errors->has('notes')) aria-invalid="true" @endif placeholder="Add an update note">{{ old('notes') }}</textarea>
                            <div id="update-note-counter" class="status-note-counter" aria-live="polite">{{ mb_strlen(old('notes', '')) }} / 300 characters</div>
                            <div id="update-note-error" class="status-modal-error" role="alert" @if (! $errors->has('notes')) hidden @endif>@error('notes'){{ $message }}@enderror</div>
                            <div id="update-modal-error" class="status-modal-error" role="alert" @if (! $errors->has('update')) hidden @endif>@error('update'){{ $message }}@enderror</div>
                            <div class="status-modal-actions">
                                <button type="button" class="button secondary dispatcher-view-button" data-modal-dismiss>Cancel</button>
                                <button id="update-confirm-button" type="submit" class="button secondary dispatcher-view-button status-confirm-button">Update</button>
                            </div>
                        </form>
                    </section>
                </div>

                <h2 style="margin-top: 24px">Incident Summary</h2>
                <dl class="detail-list">
                    <dt>Reported by</dt>
                    <dd>
                        <strong>{{ $incident->reporter?->name ?? 'Unknown volunteer' }}</strong>
                        @if ($incident->reporter?->volunteerProfile?->barangay || $incident->reporter?->contact_number)
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                @if ($incident->reporter?->volunteerProfile?->barangay)
                                    Barangay: {{ $incident->reporter->volunteerProfile->barangay }}
                                @endif
                                @if ($incident->reporter?->contact_number)
                                    &bull; Contact: {{ $incident->reporter->contact_number }}
                                @endif
                            </div>
                        @endif
                    </dd>
                    <dt>Reported at</dt>
                    <dd>{{ ($incident->reported_at ?? $incident->created_at)->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</dd>
                    <dt>Category</dt><dd>{{ $incident->category }}</dd>
                    <dt>Affected persons</dt><dd>{{ $incident->affected_population ?? $incident->persons_count }}</dd>
                    <dt>Barangay</dt><dd>{{ $incident->barangay }}</dd>
                    <dt>Nearest landmark</dt><dd>{{ $incident->nearest_landmark ?? $incident->landmark ?? 'Not provided' }}</dd>
                    <dt>Impact radius</dt><dd>{{ $incident->impact_radius ? $incident->impact_radius.' metres' : 'Not provided' }}</dd>
                    <dt>Coordinates</dt><dd>{{ $incident->latitude }}, {{ $incident->longitude }}</dd>
                    <dt>Preliminary severity</dt><dd><span id="incident-current-severity" class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></dd>
                    <dt>Dispatched team</dt><dd id="incident-dispatched-team">{{ $incident->dispatchedTeam?->name ?? 'Not yet assigned' }}</dd>
                    <dt>Notes</dt><dd>{{ $incident->notes ?? 'No notes provided.' }}</dd>
                </dl>

                <h2 style="margin-top: 24px">Status & Activity History</h2>
                <div class="history-table-wrapper">
                    <table>
                        <thead><tr><th>Status</th><th>Changed by</th><th>Timestamp</th><th>Notes</th></tr></thead>
                        <tbody data-status-history-body>
                            @forelse ($incident->history as $entry)
                                <tr>
                                    <td>
                                        @if ($entry->entry_type === 'update')
                                            <span class="badge update-tag">Update</span>
                                        @elseif ($entry->category === 'Resource cancelled')
                                            <span class="badge status-cancelled">Cancelled Dispatch</span>
                                        @else
                                            <span class="badge status-{{ strtolower($entry->status?->name ?? 'unknown') }}">{{ $entry->status?->name ?? 'Unknown' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->dispatcher?->name ?? 'System' }}</td>
                                    <td>{{ $entry->changed_at->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                                    <td>
                                        @if ($entry->notes)
                                            @php
                                                $historyTeamName = $entry->responseTeam?->name ?? $entry->external_team_name ?? '';
                                                if ($entry->status?->name === 'Dispatched' && $entry->category !== 'Resource cancelled') {
                                                    $dispatchedTeamNames = $incident->responseTeams
                                                        ->filter(fn ($team) => (int) $team->pivot->dispatched_by === (int) $entry->changed_by
                                                            && $team->pivot->dispatched_at
                                                            && \Carbon\Carbon::parse($team->pivot->dispatched_at)->equalTo($entry->changed_at))
                                                        ->pluck('name')
                                                        ->implode(', ');
                                                    $historyTeamName = $dispatchedTeamNames ?: $historyTeamName;
                                                }
                                                if ($entry->category === 'Resource cancelled' && str_contains($entry->notes, 'Cancelled dispatch for: ')) {
                                                    $historyTeamName = \Illuminate\Support\Str::between($entry->notes, 'Cancelled dispatch for: ', '. Reason:');
                                                }
                                            @endphp
                                            <button type="button" class="history-notes-view-btn"
                                                @if ($incident->status !== 'Completed') data-update-url="{{ route('dispatcher.incidents.histories.update', [$incident, $entry]) }}" @endif
                                                data-notes="{{ e($entry->notes) }}"
                                                data-team-name="{{ $historyTeamName }}"
                                                data-status="{{ $entry->status?->name ?? 'Unknown' }}"
                                                data-entry-type="{{ $entry->entry_type }}"
                                                data-category="{{ $entry->category ?? '' }}"
                                                data-changed-by="{{ $entry->dispatcher?->name ?? 'System' }}"
                                                data-date="{{ $entry->changed_at->timezone('Asia/Manila')->format('M d, Y') }}"
                                                data-time="{{ $entry->changed_at->timezone('Asia/Manila')->format('h:i A') }}"
                                                aria-label="View note for {{ $entry->status?->name ?? 'Unknown' }} on {{ $entry->changed_at->timezone('Asia/Manila')->format('M d, Y') }}">View</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-history><td colspan="4">No status changes recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Notes Modal --}}
                <div id="history-notes-modal" class="status-modal" role="presentation" hidden>
                    <div class="status-modal-backdrop" data-modal-dismiss></div>
                    <section class="status-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="history-notes-modal-title">
                        <h2 id="history-notes-modal-title">Notes</h2>
                        <div class="history-notes-meta-list">
                            <div id="history-notes-team-row" class="history-notes-meta-row" hidden>
                                <strong>Team(s):</strong>
                                <span id="history-notes-team"></span>
                            </div>
                            <div class="history-notes-meta-row">
                                <strong>Status:</strong>
                                <span>
                                    <span id="history-notes-status" class="badge"></span>
                                    <span id="history-notes-category" class="update-category" hidden></span>
                                </span>
                            </div>
                            <div class="history-notes-meta-row">
                                <strong>Updated by:</strong>
                                <span id="history-notes-changed-by"></span>
                            </div>
                            <div class="history-notes-meta-row">
                                <strong>Date and time:</strong>
                                <span id="history-notes-timestamp"></span>
                            </div>
                        </div>
                        <div class="field status-modal-field" style="margin-top: 12px;">
                            <label for="history-notes-edit-input"><strong>Notes:</strong></label>
                            <textarea id="history-notes-edit-input" class="history-notes-edit-input" rows="4" maxlength="300" disabled></textarea>
                        </div>
                        <div id="history-notes-edit-error" class="status-modal-error" role="alert" hidden></div>
                        <div class="status-modal-actions">
                            <button type="button" id="history-notes-edit-btn" class="button secondary dispatcher-view-button">Edit</button>
                            <button type="button" id="history-notes-save-btn" class="button secondary dispatcher-view-button status-confirm-button" hidden>Save</button>
                            <button type="button" id="history-notes-cancel-btn" class="button secondary dispatcher-view-button" hidden>Cancel</button>
                            <button type="button" id="history-notes-close-btn" class="button secondary dispatcher-view-button" data-modal-dismiss>Close</button>
                        </div>
                    </section>
                </div>
            </section>
        </div>

        @include('dispatcher.incidents.partials.media-and-map')
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/dispatcher-status-confirmation.js') }}" defer></script>
    <script src="{{ asset('js/dispatcher-incident-update.js') }}" defer></script>
    <script src="{{ asset('js/dispatch-modal.js') }}" defer></script>
    <script src="{{ asset('js/dispatcher-incident-completion.js') }}" defer></script>
    <script src="{{ asset('js/dispatcher-incident-history.js') }}" defer></script>
    <script src="{{ asset('js/dispatcher-incident-history-notes.js') }}" defer></script>
    <script src="{{ asset('js/dispatcher-incident-dropdowns.js') }}" defer></script>
@endpush

@push('styles')
    @include('dispatcher.incidents.partials.status-styles')
    <link rel="stylesheet" href="{{ asset('css/dispatcher-incident-history.css') }}?v=20261128-3">
    <link rel="stylesheet" href="{{ asset('css/dispatcher-incident-ui.css') }}?v=20261128-3">
@endpush
