<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
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
            'category' => ['required', 'string', 'in:Flood,Earthquake,Landslide,Fire'],
            'barangay' => ['required', 'string', 'max:100'],
            'nearest_landmark' => ['required', 'string', 'max:255'],
            'affected_population' => ['required', 'integer', 'min:0', 'max:1000000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'impact_radius' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'media' => ['nullable', 'array', 'max:5'],
            'media.*' => [
                'file',
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime',
                'max:25600',
            ],
        ];
    }
}
