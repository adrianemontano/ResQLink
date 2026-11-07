<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active === true
            && $this->user()->hasRole('volunteer')
            && $this->user()->volunteerProfile?->verification_status === 'verified';
    }

    public function rules(): array
    {
        return [
            'barangay' => ['sometimes', 'string', 'max:100'],
            'nearest_landmark' => ['sometimes', 'string', 'max:255'],
            'affected_population' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'impact_radius' => ['sometimes', 'numeric', 'gt:0', 'max:100000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}