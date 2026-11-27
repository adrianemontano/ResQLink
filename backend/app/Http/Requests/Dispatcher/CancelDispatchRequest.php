<?php

namespace App\Http\Requests\Dispatcher;

use Illuminate\Foundation\Http\FormRequest;

class CancelDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('dispatcher') === true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Give a reason for cancelling this dispatch.',
        ];
    }
}
