<?php

namespace App\Http\Requests\Dispatcher;

use App\Rules\AvailableTeam;
use Illuminate\Foundation\Http\FormRequest;

class DispatchTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('dispatcher') === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // Excludes inactive teams and teams already on an open incident,
            // including this one.
            'team_id' => ['required', 'integer', new AvailableTeam()],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'team_id.required' => 'Choose the team to dispatch.',
        ];
    }
}
