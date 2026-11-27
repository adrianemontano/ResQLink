<section class="chart-card" aria-label="Dispatches by team">
    <h2 class="chart-title">Dispatches by team</h2>
    <p class="text-muted text-small">Teams sent to incidents during this period. Time to dispatch is measured from when the incident was reported.</p>

    @if ($byTeam->isEmpty())
        <p>No teams have been created yet.</p>
    @else
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Team lead</th>
                        <th>Members</th>
                        <th>Dispatches</th>
                        <th>Cancelled</th>
                        <th>Incidents</th>
                        <th>Completed</th>
                        <th>Avg. time to dispatch</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byTeam as $row)
                        <tr>
                            <td data-label="Team">
                                {{ $row['team'] }}
                                @unless ($row['is_active'])
                                    <span class="badge inactive">Inactive</span>
                                @endunless
                            </td>
                            <td data-label="Team lead">{{ $row['team_lead'] }}</td>
                            <td data-label="Members">{{ $row['members'] }}</td>
                            <td data-label="Dispatches">{{ $row['dispatches'] }}</td>
                            <td data-label="Cancelled">{{ $row['cancelled'] }}</td>
                            <td data-label="Incidents">{{ $row['incidents'] }}</td>
                            <td data-label="Completed">{{ $row['completed'] }}</td>
                            <td data-label="Avg. time to dispatch">{{ $row['average_dispatch_minutes'] === null ? '—' : $row['average_dispatch_minutes'].' min' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
