<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Incident> */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'volunteer_id' => User::factory(),
            'reported_by' => fn (array $attributes) => $attributes['volunteer_id'],
            'category' => fake()->randomElement(['Flood', 'Earthquake', 'Landslide', 'Fire']),
            'barangay' => fake()->citySuffix().' Barangay',
            'nearest_landmark' => fake()->streetName(),
            'affected_population' => fake()->numberBetween(1, 100),
            'persons_count' => fn (array $attributes) => $attributes['affected_population'],
            'latitude' => fake()->latitude(4, 21),
            'longitude' => fake()->longitude(116, 127),
            'impact_radius' => fake()->numberBetween(50, 1500),
            'notes' => fake()->optional()->sentence(),
            'severity' => 'Moderate',
            'status' => 'Reported',
            'reported_at' => now(),
        ];
    }
}
