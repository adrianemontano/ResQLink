@props(['incident', 'teams' => collect(), 'canManage' => false])

@php
    $dispatches = $incident->dispatches;
    $activeTeamIds = $dispatches->filter->isActive()->pluck('team_id')->all();
    $availableTeams = $teams->reject(fn ($team) => in_array($team->id, $activeTeamIds, true));
    $canDispatchMore = $canManage && $incident->status === 'Dispatched';
    $formatTime = fn ($time) => $time?->timezone('Asia/Manila')->format('M d, Y, h:i A');
@endphp

<section class="card compact stack-gap incident-dispatches-card">
    <h2 class="card-title">Dispatched Teams</h2>

    @if ($dispatches->isEmpty())
        <p class="empty-panel">No team has been dispatched to this incident yet.</p>
    @else
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Dispatched</th>
                        <th>Status</th>
                        @if ($canManage)<th>Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dispatches as $dispatch)
                        <tr>
                            <td data-label="Team">
                                <strong>{{ $dispatch->team?->name ?? 'Deleted team' }}</strong>
                                @if ($dispatch->team?->teamLead)
                                    <small class="dispatch-subline">Lead: {{ $dispatch->team->teamLead->name }}</small>
                                @endif
                            </td>
                            <td data-label="Dispatched">
                                {{ $formatTime($dispatch->dispatched_at) }}
                                <small class="dispatch-subline">by {{ $dispatch->dispatcher?->name ?? 'Unknown' }}</small>
                            </td>
                            <td data-label="Status">
                                @if ($dispatch->isActive())
                                    <span class="badge active">Active</span>
                                @else
                                    <span class="badge inactive">Cancelled</span>
                                    <small class="dispatch-subline">{{ $formatTime($dispatch->cancelled_at) }} by {{ $dispatch->canceller?->name ?? 'Unknown' }}: {{ $dispatch->cancellation_reason }}</small>
                                @endif
                            </td>
                            @if ($canManage)
                                <td data-label="Action">
                                    @if ($dispatch->isActive() && $incident->status !== 'Completed')
                                        <details class="dispatch-cancel">
                                            <summary class="button secondary dispatcher-view-button">Cancel</summary>
                                            <form method="POST" action="{{ route('dispatcher.incidents.dispatches.cancellation', [$incident, $dispatch]) }}" class="dispatch-cancel-form" novalidate>
                                                @csrf
                                                @method('PATCH')
                                                <label for="cancellation_reason_{{ $dispatch->id }}">Reason <span class="required-asterisk" aria-hidden="true">*</span></label>
                                                <input id="cancellation_reason_{{ $dispatch->id }}" name="cancellation_reason" maxlength="300" required>
                                                <button type="submit" class="button danger dispatcher-view-button">Confirm cancel</button>
                                            </form>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($canManage)
        @error('cancellation_reason')
            <div class="error-text" role="alert">{{ $message }}</div>
        @enderror
    @endif

    @if ($canDispatchMore)
        <form method="POST" action="{{ route('dispatcher.incidents.dispatches.store', $incident) }}" class="incident-dispatch-form" novalidate>
            @csrf
            <h3>Dispatch another team</h3>
            <div class="field">
                <label for="dispatch_team_id">Team <span class="required-asterisk" aria-hidden="true">*</span></label>
                <select id="dispatch_team_id" name="team_id" required>
                    <option value="">Choose a team</option>
                    @foreach ($availableTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->dispatchLabel($incident->barangay) }} · {{ $team->members_count }} members</option>
                    @endforeach
                </select>
                @error('team_id')
                    <div class="error-text" role="alert">{{ $message }}</div>
                @enderror
            </div>
            <div class="field">
                <label for="dispatch_notes">Note</label>
                <input id="dispatch_notes" name="notes" maxlength="300" placeholder="Why is another team needed?">
            </div>
            <button type="submit" class="button" @disabled($availableTeams->isEmpty())>Dispatch team</button>
            @if ($availableTeams->isEmpty())
                <small class="dispatch-subline">No team is available. Teams already dispatched to an open incident are hidden until that incident is completed or the dispatch is cancelled.</small>
            @endif
        </form>
    @elseif ($canManage && $incident->status !== 'Completed' && $incident->status !== 'Dispatched')
        <p class="text-muted text-small">Choose the first team when you move this incident to Dispatched.</p>
    @endif
</section>
