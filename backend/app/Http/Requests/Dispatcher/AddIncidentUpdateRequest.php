<?php

namespace App\Http\Requests\Dispatcher;

use App\Models\IncidentHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddIncidentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('dispatcher') === true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(IncidentHistory::UPDATE_CATEGORIES)],
            'notes' => ['required', 'string', 'max:300'],
        ];
    }
}
