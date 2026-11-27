@php
    $selectedMemberIds = array_map('intval', (array) old('member_ids', $team?->members->pluck('id')->all() ?? []));
    $selectedLeadId = (int) old('team_lead_id', $team?->team_lead_id);
    // Responders already assigned to a different team cannot be selected here.
    $assignedElsewhere = fn ($responder) => $responder->teams->first(fn ($memberTeam) => $memberTeam->id !== $team?->id);
@endphp

<div class="field">
    <label for="name">Team Name <span class="required-asterisk" aria-hidden="true">*</span></label>
    <input id="name" name="name" value="{{ old('name', $team?->name) }}" maxlength="255" required>
    @error('name')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="barangay">Barangay</label>
    <input id="barangay" name="barangay" value="{{ old('barangay', $team?->barangay) }}" maxlength="100">
    @error('barangay')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <span class="field-label">Team Members <span class="required-asterisk" aria-hidden="true">*</span></span>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Barangay Responder</th>
                    <th>Barangay</th>
                    <th>Account</th>
                    <th>Current Team</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($responders as $responder)
                    @php($otherTeam = $assignedElsewhere($responder))
                    <tr>
                        <td>
                            <input id="member_{{ $responder->id }}" name="member_ids[]" type="checkbox" value="{{ $responder->id }}"
                                @checked(in_array($responder->id, $selectedMemberIds, true)) @disabled($otherTeam !== null)
                                aria-label="Add {{ $responder->name }} to the team">
                        </td>
                        <td><label for="member_{{ $responder->id }}">{{ $responder->name }}</label></td>
                        <td>{{ $responder->volunteerProfile?->barangay ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $responder->is_active ? 'active' : 'inactive' }}">
                                {{ $responder->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $otherTeam?->name ?? ($team && $responder->teams->isNotEmpty() ? 'This team' : 'None') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No Barangay Responder accounts exist yet. Create them in User Management first.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @error('member_ids')
        <div class="error-text">{{ $message }}</div>
    @enderror
    @error('member_ids.*')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="team_lead_id">Team Lead <span class="required-asterisk" aria-hidden="true">*</span></label>
    <select id="team_lead_id" name="team_lead_id" required>
        <option value="">Choose one of the selected members</option>
        @foreach ($responders as $responder)
            @continue($assignedElsewhere($responder) !== null)
            <option value="{{ $responder->id }}" @selected($selectedLeadId === $responder->id)>{{ $responder->name }}</option>
        @endforeach
    </select>
    @error('team_lead_id')
        <div class="error-text">{{ $message }}</div>
    @enderror
</div>

<div class="inline-field">
    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $team?->is_active ?? true))>
    <label for="is_active">Active team</label>
</div>
