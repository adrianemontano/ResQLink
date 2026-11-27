<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $teamId = $this->teamId();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('teams', 'name')->ignore($teamId)],
            'barangay' => ['nullable', 'string', 'max:100'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn(
                    'role_id',
                    Role::query()->where('name', 'volunteer')->select('id'),
                )),
                // A Barangay Responder can belong to only one team.
                Rule::unique('team_user', 'user_id')->where(
                    fn ($query) => $teamId === null ? $query : $query->where('team_id', '!=', $teamId),
                ),
            ],
            'team_lead_id' => ['required', 'integer', Rule::in($this->memberIds())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_ids.required' => 'Select at least one Barangay Responder for the team.',
            'member_ids.*.exists' => 'Only Barangay Responder accounts can be team members.',
            'member_ids.*.unique' => 'A selected Barangay Responder already belongs to another team.',
            'team_lead_id.required' => 'Choose a team lead.',
            'team_lead_id.in' => 'The team lead must be one of the selected team members.',
        ];
    }

    /**
     * The team being edited, or null when creating a team.
     */
    protected function teamId(): ?int
    {
        return null;
    }

    /**
     * @return array<int, int>
     */
    private function memberIds(): array
    {
        return array_map('intval', array_filter((array) $this->input('member_ids', []), 'is_numeric'));
    }
}
