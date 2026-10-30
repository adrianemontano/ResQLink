<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_incident_analytics_and_recent_records(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create([
            'role_id' => Role::query()->where('slug', 'admin')->value('id'),
        ]);
        Incident::factory()->create([
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'status' => 'Reported',
            'severity' => 'High',
            'reported_at' => now()->subMonth(),
        ]);
        Incident::factory()->create([
            'category' => 'Fire',
            'barangay' => 'Mabolo',
            'status' => Incident::STATUS_COMPLETED,
            'severity' => 'Critical',
            'reported_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Incident trend')
            ->assertSee('Incidents by category')
            ->assertSee('Incidents by status')
            ->assertSee('Severity mix')
            ->assertSee('Recent incidents')
            ->assertSee('INC-0001')
            ->assertSee('Flood')
            ->assertSee('Lahug')
            ->assertSee('Completed');
    }
}
