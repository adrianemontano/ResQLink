@extends('layouts.dashboard', ['title' => $team['name'] . ' | Team Availability'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/availability.css') }}?v=20261127b">
@endpush

@section('content')
    @php
        $backRoute = request()->routeIs('dispatcher.*')
            ? route('dispatcher.teams.availability')
            : route('admin.teams.availability');
    @endphp

    <a class="incident-back-link" href="{{ $backRoute }}">← Back to Team Availability</a>

    <section class="card" style="margin-top: 12px;">
        <div class="availability-modal-header">
            <div>
                <h1 style="margin: 0;">{{ $team['name'] }}</h1>
                <p class="availability-subtext">{{ $team['type'] }}</p>
            </div>
            <span class="availability-status-badge {{ $team['status'] }}">
                @if ($team['status'] === 'available') ● Active
                @elseif ($team['status'] === 'on_mission') ● Dispatched @if($team['active_inc_no']) ({{ $team['active_inc_no'] }}) @endif
                @else ● Inactive
                @endif
            </span>
        </div>

        <div class="availability-modal-info">
            <div><strong>Team Leader:</strong> {{ $team['team_leader'] }}</div>
            <div><strong>Contact:</strong> {{ $team['contact_number'] }}</div>
            <div><strong>Completed Responses:</strong> {{ $team['total_responses'] }}</div>
            <div><strong>Avg. Response Time:</strong> {{ $team['avg_duration'] }}</div>
        </div>

        <div class="availability-modal-section">
            <h2>Members</h2>
            @if (count($team['members']) > 0)
                <ol class="availability-members-list">
                    @foreach ($team['members'] as $member)
                        <li>{{ $member }}</li>
                    @endforeach
                </ol>
            @else
                <p class="availability-subtext">No members listed yet.</p>
            @endif
        </div>

        <div class="availability-modal-section" style="margin-top: 24px;">
            <h2>Activity Log</h2>
            <div class="table-scroll">
                <table class="availability-log-table">
                    <thead>
                        <tr>
                            <th>Incident</th>
                            <th>Responded On</th>
                            <th>Completed On</th>
                            <th>Resolved In</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($team['activity_log'] as $entry)
                            <tr>
                                <td>
                                    <strong>{{ $entry['inc_no'] }}</strong>
                                    <div class="availability-subtext">{{ $entry['category'] }} · {{ $entry['barangay'] }}</div>
                                </td>
                                <td>{{ $entry['responded_on'] }}</td>
                                <td>{{ $entry['completed_on'] }}</td>
                                <td><strong>{{ $entry['duration'] }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No dispatch activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
