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

                <h2 class="incident-section-title">Update Status</h2>
                @php
                    $statuses = ['Reported', 'Received', 'Dispatched', 'Completed'];
                    $currentIndex = array_search($incident->status, $statuses);
                    $nextSteps = [
                        'Reported' => ['name' => 'Received', 'class' => 'btn-status-received', 'label' => 'Mark as received'],
                        'Received' => ['name' => 'Dispatched', 'class' => 'btn-status-dispatched', 'label' => 'Dispatch response units'],
                        'Dispatched' => ['name' => 'Completed', 'class' => 'btn-status-completed', 'label' => 'Mark as completed'],
                    ];
                    $next = $nextSteps[$incident->status] ?? null;
                    $modalStatus = old('status', $next['name'] ?? '');
                    $isCompleted = $incident->status === 'Completed';
                @endphp

                {{-- Status Stepper --}}
                <div class="status-stepper" aria-label="Incident status progress">
                    @foreach ($statuses as $index => $status)
                        <div class="stepper-step {{ $index < $currentIndex ? 'stepper-past' : ($index === $currentIndex ? 'stepper-current' : 'stepper-future') }}">
                            <span class="stepper-dot"></span>
                            <span class="stepper-label">{{ $status }}</span>
                        </div>
                        @if (! $loop->last)
                            <div class="stepper-connector {{ $index < $currentIndex ? 'stepper-connector-past' : '' }}"></div>
                        @endif
                    @endforeach
                </div>

                <div id="incident-status-actions" class="incident-status-actions">
                    @if ($isCompleted)
                        <p class="status-complete-message">This incident is complete. No further updates are available.</p>
                    @else
                        <div class="status-actions-row">
                            @if ($next)
                                <button type="button" class="btn-status-action {{ $next['class'] }}" data-status="{{ $next['name'] }}">
                                    {{ $next['label'] }}
                                </button>
                            @endif
                            <button type="button" class="btn-add-update" id="add-update-btn">
                                + Add update
                            </button>
                        </div>
                    @endif
                </div>

                <div id="status-confirmation-modal" class="status-modal" role="presentation" hidden data-open-on-load="{{ $errors->has('status') || $errors->has('notes') || ($errors->has('team_id') && old('status')) ? 'true' : 'false' }}">
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
                            This action cannot be undone.
                        </p>

                        <form id="incident-status-form" method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}" novalidate>
                            @csrf
                            @method('PATCH')
                            <input id="status-modal-value" type="hidden" name="status" value="{{ $modalStatus }}">
                            <div id="status-modal-team-field" class="field status-modal-field" @if ($modalStatus !== 'Dispatched') hidden @endif>
                                <label for="status-modal-team">Team to dispatch <span class="required-asterisk" aria-hidden="true">*</span></label>
                                <select id="status-modal-team" name="team_id" class="status-modal-select">
                                    <option value="">Choose a team</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected((int) old('team_id') === $team->id)>{{ $team->dispatchLabel($incident->barangay) }}</option>
                                    @endforeach
                                </select>
                                @if ($teams->isEmpty())
                                    <div class="status-modal-error" role="alert">No team is available. Every active team is already dispatched; a team becomes available again when its incident is completed or its dispatch is cancelled.</div>
                                @endif
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

                <h2 class="mt-section">Incident Summary</h2>
                <dl class="detail-list">
                    <dt>Reported by</dt>
                    <dd>
                        <strong>{{ $incident->reporter?->name ?? 'Unknown volunteer' }}</strong>
                        @if ($incident->reporter?->volunteerProfile?->barangay || $incident->reporter?->contact_number)
                            <div class="text-muted text-small">
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
                    <dt>Preliminary severity</dt><dd><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></dd>
                    <dt>Notes</dt><dd>{{ $incident->notes ?? 'No notes provided.' }}</dd>
                </dl>

                <h2 class="mt-section">Status &amp; Activity History</h2>
                <div class="history-table-wrapper">
                    <table>
                        <thead><tr><th>Status</th><th>Changed by</th><th>Timestamp</th><th>Notes</th></tr></thead>
                        <tbody data-status-history-body>
                            @forelse ($incident->history as $entry)
                                <tr>
                                    <td>
                                        @if ($entry->entry_type === 'update')
                                            <span class="badge update-tag">Update</span>
                                        @else
                                            <span class="badge status-{{ strtolower($entry->status?->name ?? 'unknown') }}">{{ $entry->status?->name ?? 'Unknown' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->dispatcher?->name ?? 'System' }}</td>
                                    <td>{{ $entry->changed_at->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                                    <td>
                                        @if ($entry->notes)
                                            <button type="button" class="history-notes-view-btn"
                                                data-notes="{{ e($entry->notes) }}"
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
                        <div class="history-notes-meta">
                            <span id="history-notes-status" class="badge"></span>
                            <span id="history-notes-category" class="update-category" hidden></span>
                            <span id="history-notes-changed-by"></span>
                            <span id="history-notes-timestamp"></span>
                        </div>
                        <p id="history-notes-content" class="history-notes-content"></p>
                        <div class="status-modal-actions">
                            <button type="button" class="button secondary dispatcher-view-button" data-modal-dismiss>Close</button>
                        </div>
                    </section>
                </div>
            </section>
        </div>

        <div class="detail-right">
            <x-incident-dispatches :incident="$incident" :teams="$teams" :can-manage="true" />

            <x-incident-photo-card :incident="$incident" route-prefix="dispatcher.incidents.photo" />

            <section class="card compact map-card">
                <h2 class="card-title">Incident Location</h2>
                @if ($incident->latitude !== null && $incident->longitude !== null)
                    <div class="map-wrap">
                        <x-incident-map
                            map-id="incident-mini-map"
                            :focus-lat="$incident->latitude"
                            :focus-lng="$incident->longitude"
                            :focus-incident-id="$incident->id"
                            :incident-status="$incident->status"
                            :show-search="false"
                        />
                    </div>
                @else
                    <p class="empty-panel">No location data available.</p>
                @endif
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/dispatcher-status-confirmation.js') }}" defer></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dispatcher-status-confirmation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dispatcher-incident-history.css') }}">
@endpush
