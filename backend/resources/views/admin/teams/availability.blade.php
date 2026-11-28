@extends('layouts.dashboard', ['title' => 'Team Availability | ResQLink'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/availability.css') }}?v=20261127b">
@endpush

@section('content')
    <div class="header-row availability-header-row">
        <div>
            <h1>Team Availability</h1>
            <p>Click View on a team to open its members and response activity logs.</p>
        </div>
        <form class="availability-search" method="GET" action="{{ url()->current() }}" role="search">
            <label class="sr-only" for="team-availability-search">Search by team name or team leader</label>
            <input
                id="team-availability-search"
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Search team or team leader"
                maxlength="100"
            >
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <button type="submit" class="button secondary dispatcher-view-button">Search</button>
        </form>
    </div>

    @php
        $availabilityUrl = fn (string $status): string => request()->fullUrlWithQuery([
            'status' => $status,
            'page' => 1,
        ]);
    @endphp

    <nav class="status-filter-chips" aria-label="Filter teams by status" style="margin-bottom: 16px;">
        <a href="{{ $availabilityUrl('all') }}" class="status-filter-chip {{ $selectedStatus === 'all' ? 'active' : '' }}" @if ($selectedStatus === 'all') aria-current="page" @endif>
            <span>All</span>
            <span class="status-filter-count">{{ $summary['all'] }}</span>
        </a>
        <a href="{{ $availabilityUrl('available') }}" class="status-filter-chip {{ $selectedStatus === 'available' ? 'active' : '' }}" @if ($selectedStatus === 'available') aria-current="page" @endif>
            <span>Active</span>
            <span class="status-filter-count">{{ $summary['available'] }}</span>
        </a>
        <a href="{{ $availabilityUrl('on_mission') }}" class="status-filter-chip {{ $selectedStatus === 'on_mission' ? 'active' : '' }}" @if ($selectedStatus === 'on_mission') aria-current="page" @endif>
            <span>Dispatched</span>
            <span class="status-filter-count">{{ $summary['on_mission'] }}</span>
        </a>
        <a href="{{ $availabilityUrl('inactive') }}" class="status-filter-chip {{ $selectedStatus === 'inactive' ? 'active' : '' }}" @if ($selectedStatus === 'inactive') aria-current="page" @endif>
            <span>Inactive</span>
            <span class="status-filter-count">{{ $summary['inactive'] }}</span>
        </a>
    </nav>

    <section class="card">
        <div class="table-scroll">
            <table class="availability-table">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Team Leader</th>
                        <th>Status</th>
                        <th>Responses</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="availability-table-body">
                    @forelse ($teams as $team)
                        @php
                            $showUrl = request()->routeIs('dispatcher.*')
                                ? route('dispatcher.teams.availability.show', $team['id'])
                                : route('admin.teams.availability.show', $team['id']);
                        @endphp
                        <tr class="availability-row" data-href="{{ $showUrl }}">
                            <td>
                                <strong>{{ $team['name'] }}</strong>
                                <div class="availability-subtext">{{ $team['type'] }}</div>
                            </td>
                            <td>{{ $team['team_leader'] }}</td>
                            <td>
                                <span class="availability-status-badge {{ $team['status'] }}">
                                    @if ($team['status'] === 'available') ● Active
                                    @elseif ($team['status'] === 'on_mission') ● Dispatched @if($team['active_inc_no']) ({{ $team['active_inc_no'] }}) @endif
                                    @else ● Inactive
                                    @endif
                                </span>
                            </td>
                            <td>
                                {{ $team['total_responses'] }}
                                @if ($team['avg_duration'] !== '—')
                                    <span class="availability-subtext">Avg: {{ $team['avg_duration'] }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $showUrl }}" class="button secondary dispatcher-view-button">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                @if ($search !== '')
                                    No teams match "{{ $search }}".
                                @else
                                    No response teams found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-resqlink-pagination :paginator="$teams" :per-page="$perPage" />
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rows = [...document.querySelectorAll('.availability-row')];

            rows.forEach((row) => {
                row.addEventListener('click', (e) => {
                    if (e.target.closest('a')) return;
                    if (row.dataset.href) window.location.href = row.dataset.href;
                });
            });
        });
    </script>
@endsection
