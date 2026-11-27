<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
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
            'category' => ['required', 'string', 'in:Flood,Earthquake,Landslide,Fire'],
            'barangay' => ['required', 'string', 'max:100'],
            'nearest_landmark' => ['required', 'string', 'max:255'],
            'affected_population' => ['required', 'integer', 'min:0', 'max:1000000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'impact_radius' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select category.',
            'category.in' => 'Please select a valid category.',
            'barangay.required' => 'Please enter barangay.',
            'nearest_landmark.required' => 'Please enter nearest landmark.',
            'affected_population.required' => 'Please enter affected persons.',
            'affected_population.integer' => 'Affected persons must be a number.',
            'affected_population.min' => 'Affected persons cannot be negative.',
            'latitude.required' => 'Please enter latitude.',
            'latitude.numeric' => 'Latitude must be a number.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.required' => 'Please enter longitude.',
            'longitude.numeric' => 'Longitude must be a number.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
            'impact_radius.required' => 'Please enter impact radius.',
            'impact_radius.numeric' => 'Impact radius must be a number.',
            'impact_radius.gt' => 'Impact radius must be greater than zero.',
            'photo.image' => 'Photo must be an image.',
            'photo.mimes' => 'Photo must be JPEG, JPG, or PNG.',
            'photo.max' => 'Photo must not exceed 5 MB.',
            'photo.uploaded' => 'The photo could not be uploaded, usually because it is too large. Please take it again.',
        ];
    }
}
