<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_analytics_for_the_signed_in_volunteer_only(): void
    {
        $this->seed(RoleSeeder::class);
        $volunteerRoleId = Role::query()->where('slug', 'volunteer')->value('id');
        $volunteer = User::factory()->create(['role_id' => $volunteerRoleId]);
        $otherVolunteer = User::factory()->create(['role_id' => $volunteerRoleId]);
        $volunteer->volunteerProfile()->create([
            'barangay' => 'Lahug',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        Incident::create([
            'volunteer_id' => $volunteer->id,
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'status' => 'Reported',
            'reported_at' => now()->subMonth(),
        ]);
        Incident::create([
            'reported_by' => $volunteer->id,
            'category' => 'Earthquake',
            'barangay' => 'Mabolo',
            'status' => Incident::STATUS_COMPLETED,
            'reported_at' => now(),
        ]);
        Incident::create([
            'volunteer_id' => $otherVolunteer->id,
            'reported_by' => $otherVolunteer->id,
            'category' => 'Fire',
            'barangay' => 'Banilad',
            'status' => 'Reported',
            'reported_at' => now(),
        ]);

        $this->actingAs($volunteer)
            ->get(route('volunteer.dashboard'))
            ->assertOk()
            ->assertSee('Your response impact')
            ->assertSee('Reporting trend')
            ->assertSee('Incident categories')
            ->assertSee('Recent reports')
            ->assertSee('INC-0001')
            ->assertSee('INC-0002')
            ->assertDontSee('INC-0003')
            ->assertSee('Flood')
            ->assertSee('Earthquake')
            ->assertDontSee('Fire');
    }
}
