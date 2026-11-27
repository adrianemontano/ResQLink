@extends('layouts.dashboard', ['title' => 'Teams | ResQLink'])

@section('content')

    <div class="header-row">
        <div>
            <h1>Teams</h1>
            <p>Barangay Responder teams and their assigned team leads.</p>
        </div>
        <a class="button" href="{{ route('admin.teams.create') }}">Create Team</a>
    </div>

    <form class="card filter-row admin-user-filter-row" method="GET" action="{{ route('admin.teams.index') }}">
        <label>Status
            <select name="status">
                <option value="all" @selected($statusFilter === 'all')>All</option>
                <option value="active" @selected($statusFilter === 'active')>Active</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
            </select>
        </label>
        <div class="filter-actions-group">
            <button class="button secondary dispatcher-view-button" type="submit">Filter</button>
            <a class="button secondary dispatcher-view-button" href="{{ route('admin.teams.index') }}">Clear</a>
        </div>
    </form>

    <section class="card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Team</th>
                        <th>Barangay</th>
                        <th>Team Lead</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teams as $team)
                        <tr>
                            <td>{{ $team->name }}</td>
                            <td>{{ $team->barangay ?? '—' }}</td>
                            <td>{{ $team->teamLead?->name ?? 'Not assigned' }}</td>
                            <td>{{ $team->members_count }}</td>
                            <td>
                                <span class="badge {{ $team->is_active ? 'active' : 'inactive' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="actions inline">
                                    <a class="button secondary dispatcher-view-button" href="{{ route('admin.teams.edit', $team) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.teams.activation', $team) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="button dispatcher-view-button {{ $team->is_active ? 'danger' : 'secondary' }}">
                                            {{ $team->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No teams have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="records-pagination">
            {{ $teams->links() }}
        </div>
    </section>

@endsection
