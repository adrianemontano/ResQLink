@php
    $frequencyMaximum = max(1, $teamFrequency->max('recent') ?? 0);
@endphp

<section class="card admin-analytics-card admin-team-frequency-card" aria-label="Team dispatch frequency">
    <div class="admin-card-heading">
        <div>
            <span class="admin-eyebrow">Last {{ $teamFrequencyDays }} days</span>
            <h2>Team dispatch frequency</h2>
        </div>
        <a class="button secondary dispatcher-view-button" href="{{ route('admin.teams.index') }}">Manage teams</a>
    </div>

    <div class="admin-breakdown-list">
        @forelse ($teamFrequency as $row)
            <div class="admin-breakdown-row admin-team-frequency-row">
                <div class="admin-breakdown-label">
                    <span>
                        {{ $row['team'] }}
                        @if (! $row['is_active'])
                            <span class="badge inactive">Inactive</span>
                        @elseif ($row['on_call'])
                            <span class="badge status-dispatched">On a call</span>
                        @else
                            <span class="badge active">Available</span>
                        @endif
                    </span>
                    <strong>{{ $row['recent'] }} {{ \Illuminate\Support\Str::plural('dispatch', $row['recent']) }}</strong>
                </div>
                <div class="admin-breakdown-track">
                    <span style="width: {{ ($row['recent'] / $frequencyMaximum) * 100 }}%; --bar-color: var(--status-dispatched)"></span>
                </div>
                <small class="admin-team-frequency-meta">
                    Lead: {{ $row['team_lead'] }}
                    · about {{ $row['per_week'] }} per week
                    · {{ $row['total'] }} all time
                    · last dispatched {{ $row['last_dispatched_at']?->diffForHumans() ?? 'never' }}
                </small>
            </div>
        @empty
            <div class="admin-empty-state">
                No teams yet. <a href="{{ route('admin.teams.create') }}">Create a team</a> to track how often it is dispatched.
            </div>
        @endforelse
    </div>
</section>
