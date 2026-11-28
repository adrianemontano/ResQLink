<?php

namespace App\Http\Requests\Dispatcher;

use App\Models\IncidentHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddIncidentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['dispatcher', 'admin']) === true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(IncidentHistory::UPDATE_CATEGORIES)],
            'changed_at' => ['required', 'date'],
            'response_team_id' => ['nullable', 'integer', 'exists:response_teams,id'],
            'notes' => ['required', 'string', 'max:300'],
        ];
    }
}
