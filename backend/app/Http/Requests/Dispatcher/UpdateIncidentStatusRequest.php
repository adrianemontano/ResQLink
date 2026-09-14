<?php

namespace App\Http\Requests\Dispatcher;

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
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}