<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerIncidentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_volunteer_sees_confirmation_after_submitting_incident(): void
    {
        $this->seed([RoleSeeder::class, ReferenceDataSeeder::class]);

        $volunteer = User::factory()->create([
            'role_id' => Role::query()->where('slug', 'volunteer')->value('id'),
            'is_active' => true,
        ]);
        $volunteer->volunteerProfile()->create([
            'barangay' => 'Lahug',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($volunteer)->post(route('volunteer.incidents.store'), [
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'nearest_landmark' => 'Barangay Hall',
            'affected_population' => 25,
            'latitude' => 10.3302,
            'longitude' => 123.8988,
            'impact_radius' => 300,
            'notes' => 'Water is rising.',
        ]);

        $response->assertRedirect(route('volunteer.incidents.create'))
            ->assertSessionHas('incident', fn (array $incident): bool =>
                $incident['status'] === 'Reported'
                && $incident['severity'] === 'High'
            );

        $this->get(route('volunteer.incidents.create'))
            ->assertOk()
            ->assertSee('was submitted')
            ->assertSee('INC-0001');

        $this->assertDatabaseHas('incidents', [
            'volunteer_id' => $volunteer->id,
            'category' => 'Flood',
            'status' => 'Reported',
        ]);
    }
}
