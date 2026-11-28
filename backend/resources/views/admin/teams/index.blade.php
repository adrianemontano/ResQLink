@extends('layouts.dashboard', ['title' => 'Response Teams | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Response Teams</h1>
            <p>Manage emergency response teams available for incident dispatch.</p>
        </div>
    </div>

    <section class="card">
        <h2>Add Response Team</h2>
        <form method="POST" action="{{ route('admin.teams.store') }}" class="form-grid" style="margin-top: var(--space-3);">
            @csrf
            <label>
                <span>Team Name <span class="required-asterisk" aria-hidden="true">*</span></span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Foxtrot Medical Unit" required>
                @error('name')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Team Code</span>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. TEAM-FOXTROT">
                @error('code')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Unit Type <span class="required-asterisk" aria-hidden="true">*</span></span>
                <input type="text" name="type" value="{{ old('type') }}" placeholder="e.g. Search &amp; Rescue, Medical / EMS" required>
                @error('type')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Contact Number</span>
                <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="e.g. 0917-100-0006">
                @error('contact_number')<small>{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Team Leader</span>
                <input type="text" name="team_leader" value="{{ old('team_leader') }}" placeholder="e.g. Ben Dee">
                @error('team_leader')<small>{{ $message }}</small>@enderror
            </label>
            <label class="full-width">
                <span>Team Members (one per line or comma-separated)</span>
                <textarea name="members" rows="2" placeholder="e.g. Fred, Greg, Maria">{{ old('members') }}</textarea>
                @error('members')<small>{{ $message }}</small>@enderror
            </label>
            <div class="form-actions full-width">
                <button type="submit" class="button">Add Team</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Team Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Dispatches</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teams as $team)
                        <tr>
                            <td>{{ $team->code ?: '—' }}</td>
                            <td><strong>{{ $team->name }}</strong></td>
                            <td>{{ $team->type ?: 'General' }}</td>
                            <td>{{ $team->contact_number ?: '—' }}</td>
                            <td>{{ $team->incidents_count }}</td>
                            <td>
                                <span class="badge {{ $team->is_active ? 'active' : 'inactive' }}">
                                    {{ $team->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.teams.toggle', $team) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="button dispatcher-view-button {{ $team->is_active ? 'danger' : 'secondary' }}">
                                        {{ $team->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No response teams configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-resqlink-pagination :paginator="$teams" :per-page="$perPage" />
    </section>
@endsection
