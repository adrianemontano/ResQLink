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
                    $nextSteps = [
                        'Reported' => ['name' => 'Received', 'class' => 'btn-status-received', 'label' => 'Acknowledge & Mark as Received'],
                        'Received' => ['name' => 'Dispatched', 'class' => 'btn-status-dispatched', 'label' => 'Dispatch Response Units'],
                        'Dispatched' => ['name' => 'Completed', 'class' => 'btn-status-completed', 'label' => 'Mark as Completed / Resolved'],
                    ];
                    $next = $nextSteps[$incident->status] ?? null;
                    $modalStatus = old('status', $next['name'] ?? '');
                @endphp

                <div id="incident-status-actions" class="incident-status-actions">
                    @if ($next)
                        <button type="button" class="btn-status-action {{ $next['class'] }}" data-status="{{ $next['name'] }}">
                            {{ $next['label'] }}
                        </button>
                    @else
                        <p class="status-complete-message">This incident is complete. No further status changes are available.</p>
                    @endif
                </div>

                <div id="status-confirmation-modal" class="status-modal" role="presentation" hidden data-open-on-load="{{ $errors->has('status') || $errors->has('notes') ? 'true' : 'false' }}">
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

                        <form id="incident-status-form" method="POST" action="{{ route('dispatcher.incidents.status', $incident) }}">
                            @csrf
                            @method('PATCH')
                            <input id="status-modal-value" type="hidden" name="status" value="{{ $modalStatus }}">
                            <label for="status-modal-notes">Required note</label>
                            <textarea id="status-modal-notes" name="notes" rows="4" maxlength="300" required aria-describedby="status-note-counter status-note-error" @if ($errors->has('notes')) aria-invalid="true" @endif placeholder="Add a note about this status change">{{ old('notes') }}</textarea>
                            <div id="status-note-counter" class="status-note-counter" aria-live="polite">{{ mb_strlen(old('notes', '')) }} / 300 characters</div>
                            <div id="status-note-error" class="status-modal-error" role="alert" @if (! $errors->has('notes')) hidden @endif>@error('notes'){{ $message }}@enderror</div>
                            <div id="status-modal-error" class="status-modal-error" role="alert" @if (! $errors->has('status')) hidden @endif>@error('status'){{ $message }}@enderror</div>
                            <div class="status-modal-actions">
                                <button type="button" class="button secondary" data-modal-dismiss>Cancel</button>
                                <button id="status-confirm-button" type="submit" class="status-confirm-button">Confirm</button>
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
                    <dt>Preliminary severity</dt><dd><span class="badge severity-{{ strtolower($incident->severity ?? 'unrated') }}">{{ $incident->severity ?? 'Unrated' }}</span></dd>
                    <dt>Notes</dt><dd>{{ $incident->notes ?? 'No notes provided.' }}</dd>
                </dl>

                <h2 style="margin-top: 24px">Status & Activity History</h2>
                <table style="margin-top: 0">
                    <thead><tr><th>Status</th><th>Changed by</th><th>Timestamp</th><th>Notes</th></tr></thead>
                    <tbody data-status-history-body>
                        @forelse ($incident->history as $entry)
                            <tr>
                                <td><span class="badge status-{{ strtolower($entry->status?->name ?? 'unknown') }}">{{ $entry->status?->name ?? 'Unknown' }}</span></td>
                                <td>{{ $entry->dispatcher?->name ?? 'System' }}</td>
                                <td>{{ $entry->changed_at->timezone('Asia/Manila')->format('M d, Y, h:i A') }}</td>
                                <td>{{ $entry->notes ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr data-empty-history><td colspan="4">No status changes recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <div class="detail-right">
            {{-- Incident Photo / Attachment Card --}}
            <section class="card compact" style="margin-bottom: var(--space-4);">
                <h2 style="margin-top: 0">Incident Photo & Attachment</h2>
                @if ($incident->photos && $incident->photos->isNotEmpty())
                    @php $photo = $incident->photos->first(); @endphp
                    <div class="photo-preview-container" style="text-align: center;">
                        <a href="#photo-modal" onclick="document.getElementById('photo-modal').style.display='flex'; return false;">
                            <img class="incident-photo-preview" src="{{ route('dispatcher.incidents.photo.show', [$incident, $photo]) }}"
                                 alt="Incident Photo"
                                 style="max-width: 100%; max-height: 220px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); cursor: pointer;">
                        </a>
                        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--text-muted);">
                            <span>{{ $photo->original_name ?? 'incident-photo.jpg' }} ({{ round($photo->size / 1024, 1) }} KB)</span>
                            <a href="{{ route('dispatcher.incidents.photo.download', [$incident, $photo]) }}" class="button secondary" style="padding: 4px 8px; font-size: 11px;">
                                Download
                            </a>
                        </div>
                    </div>

                    {{-- Lightbox Modal --}}
                    <div id="photo-modal" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); align-items: center; justify-content: center;" onclick="this.style.display='none';">
                        <div style="position: relative; max-width: 90%; max-height: 90%; background: #ffffff; padding: 12px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);" onclick="event.stopPropagation();">
                            <button type="button" onclick="document.getElementById('photo-modal').style.display='none';" style="position: absolute; top: -12px; right: -12px; background: var(--rosewood); color: #fff; border: 2px solid #fff; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 16px; font-weight: bold; line-height: 1;">&times;</button>
                            <img src="{{ route('dispatcher.incidents.photo.show', [$incident, $photo]) }}" alt="Incident Photo Enlarge" style="max-width: 100%; max-height: 75vh; display: block; border-radius: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <span style="font-size: 13px; color: var(--text-muted);">{{ $photo->original_name }}</span>
                                <a href="{{ route('dispatcher.incidents.photo.download', [$incident, $photo]) }}" class="button primary" style="padding: 6px 14px; font-size: 12px;">Download Full Photo</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="padding: var(--space-4); text-align: center; color: var(--text-muted); background: var(--surface-alt); border-radius: 6px; border: 1px dashed var(--border);">
                        <svg viewBox="0 0 24 24" style="width: 36px; height: 36px; stroke: var(--text-muted); stroke-width: 1.5; fill: none; margin-bottom: 6px; opacity: 0.6;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        <p style="margin: 0; font-size: 13px; font-weight: 500;">No photo/file uploaded</p>
                        <small style="color: var(--text-muted); font-size: 11px;">The field responder did not attach an image to this report.</small>
                    </div>
                @endif
            </section>

            <section class="card compact map-card">
                <h2 style="margin-top: 0">Incident Location</h2>
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
                    <p style="color: var(--gray-500); font-size: 13px; padding: var(--space-4); text-align: center;">No location data available.</p>
                @endif
            </section>
        </div>
    </section>
    <div id="incident-status-toast" class="incident-status-toast" role="status" aria-live="polite" @if (! session('incident_status_success')) hidden @endif>
        {{ session('incident_status_success') }}
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dispatcher-status-confirmation.js') }}" defer></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dispatcher-status-confirmation.css') }}">
@endpush
