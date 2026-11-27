<?php

namespace App\Http\Requests\Dispatcher;

use App\Rules\AvailableTeam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('dispatcher') === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Reported', 'Received', 'Dispatched', 'Completed'])],
            'notes' => ['required', 'string', 'max:300'],
            // Moving to Dispatched records which team was sent.
            'team_id' => [
                'exclude_unless:status,Dispatched',
                'required',
                'integer',
                new AvailableTeam(),
            ],
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
