<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user || ! $user->is_active) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('volunteer')
            && $user->volunteerProfile?->verification_status === 'verified';
    }

    public function rules(): array
    {
        return [
            'barangay' => ['sometimes', 'string', 'max:100'],
            'nearest_landmark' => ['sometimes', 'string', 'max:255'],
            'affected_population' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'impact_radius' => ['sometimes', 'numeric', 'gt:0', 'max:100000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000', function ($attribute, $value, $fail) {
                $words = str_word_count(strip_tags((string) $value));
                if ($words > 300) {
                    $fail('The notes field must not exceed 300 words.');
                }
            }],
            'media' => ['nullable', 'array', 'max:5'],
            'media.*' => [
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime',
                'max:25600',
            ],
        ];
    }
}
