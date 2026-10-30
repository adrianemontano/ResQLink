<?php

namespace App\Http\Requests\Admin;

use App\Models\VolunteerDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVolunteerDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $managedUser = $this->route('user');

        return $this->user()?->hasRole('admin') === true
            && $managedUser?->hasRole('volunteer') === true
            && $managedUser->volunteerProfile !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'document_type' => [
                'required',
                Rule::in(array_keys(VolunteerDocument::TYPES)),
                Rule::unique('volunteer_documents', 'document_type')
                    ->where('volunteer_profile_id', $this->route('user')->getKey()),
            ],
            'document' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_type.unique' => 'This volunteer already has that document type on file.',
            'document.max' => 'The document must not be larger than 5 MB.',
            'document.mimes' => 'The document must be a PDF, JPG, or PNG file.',
        ];
    }
}
