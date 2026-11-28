<div id="manage-teams-modal" class="status-modal" role="presentation" hidden>
    <div class="status-modal-backdrop" data-modal-dismiss></div>
    <section class="manage-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="manage-modal-title">
        <header class="manage-modal-header">
            <h2 id="manage-modal-title">Manage teams - INC-{{ str_pad($incident->id, 4, '0', STR_PAD_LEFT) }}</h2>
            <p id="manage-modal-count">{{ $incident->responseTeams()->wherePivot('status', 'active')->count() }} active - {{ $incident->responseTeams()->wherePivot('status', 'cancelled')->count() }} cancelled</p>
        </header>

        <div class="manage-modal-body">
            <div class="manage-search-section">
                <input
                    type="text"
                    id="manage-team-search"
                    class="dispatch-search-input"
                    placeholder="Search team name..."
                    autocomplete="off"
                >
            </div>

            <div class="manage-tabs">
                <button type="button" class="manage-tab active" data-tab="active">Active ({{ $incident->responseTeams()->wherePivot('status', 'active')->count() }})</button>
                <button type="button" class="manage-tab" data-tab="cancelled">Cancelled ({{ $incident->responseTeams()->wherePivot('status', 'cancelled')->count() }})</button>
            </div>

            <div class="manage-select-all">
                <button type="button" id="manage-select-all">Select all on this page</button>
            </div>

            <div class="manage-team-list" id="manage-team-list">
                {{-- Active teams --}}
                <div class="manage-tab-content active" id="manage-active-content">
                    @php
                        $activeTeams = $incident->responseTeams()->wherePivot('status', 'active')->get()->sortByDesc('pivot.dispatched_at');
                    @endphp
                    @if ($activeTeams->isEmpty())
                        <div class="manage-empty">No active teams</div>
                    @else
                        @foreach ($activeTeams as $team)
                            <div class="manage-team-row" data-team-id="{{ $team->id }}">
                                <label class="manage-checkbox">
                                    <input type="checkbox" class="manage-team-checkbox" value="{{ $team->id }}">
                                </label>
                                <div class="manage-team-info">
                                    <span class="manage-team-name">{{ $team->name }}</span>
                                    <span class="manage-team-type">{{ $team->type }}</span>
                                </div>
                                <span class="manage-team-time">Dispatched {{ $team->pivot->dispatched_at ? \Carbon\Carbon::parse($team->pivot->dispatched_at)->timezone('Asia/Manila')->format('M d, h:i A') : 'N/A' }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Cancelled teams --}}
                <div class="manage-tab-content" id="manage-cancelled-content" hidden>
                    @php
                        $cancelledTeams = $incident->responseTeams()->wherePivot('status', 'cancelled')->get()->sortByDesc('pivot.cancelled_at');
                    @endphp
                    @if ($cancelledTeams->isEmpty())
                        <div class="manage-empty">No cancelled teams</div>
                    @else
                        @foreach ($cancelledTeams as $team)
                            <div class="manage-team-row cancelled-row">
                                <div class="manage-team-info">
                                    <span class="manage-team-name">{{ $team->name }}</span>
                                    <span class="manage-team-type">{{ $team->type }}</span>
                                </div>
                                <div class="manage-cancel-info">
                                    <span class="manage-cancel-time">Cancelled {{ $team->pivot->cancelled_at ? \Carbon\Carbon::parse($team->pivot->cancelled_at)->timezone('Asia/Manila')->format('M d, h:i A') : 'N/A' }}</span>
                                    <span class="manage-cancel-reason">{{ $team->pivot->cancel_reason ?? 'No reason provided' }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <footer class="manage-modal-footer">
            <div class="footer-selected-count"><span id="manage-selected-count">0</span> selected</div>
            <div class="footer-actions">
                <button type="button" class="button secondary" data-modal-dismiss>Close</button>
                <button type="button" id="manage-cancel-button" class="button danger" disabled>Cancel dispatch (0)</button>
            </div>
        </footer>
    </section>
</div>
