<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DispatcherIncidentCoordinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_can_view_and_filter_the_incident_queue(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $this->seedReferenceData();
        $this->createIncident(['category' => 'Flood', 'barangay' => 'Lahug', 'severity' => 'Critical']);
        $this->createIncident(['category' => 'Fire', 'barangay' => 'Mabolo', 'severity' => 'Low']);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.index'))
            ->assertOk()->assertSee('Flood')->assertSee('Fire');

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.index', ['category' => 'Fire']))
            ->assertOk()->assertSee('Mabolo')->assertDontSee('Lahug');
    }

    public function test_dispatcher_can_view_details_and_map_shell(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $this->seedReferenceData();
        $incident = $this->createIncident(['barangay' => 'Lahug', 'impact_radius' => 80]);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.show', $incident))
            ->assertOk()->assertSee('Incident Summary')->assertSee('Lahug')->assertSee('80')->assertSee('metres');
        $this->actingAs($dispatcher)->get(route('dispatcher.map'))
            ->assertOk()->assertSee('data-resqlink-map', false)->assertDontSee('data-marker-endpoint', false);
    }

    public function test_valid_status_update_records_dispatcher_history(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $this->seedReferenceData();
        $incident = $this->createIncident(['status' => 'Reported']);

        $this->actingAs($dispatcher)->patch(route('dispatcher.incidents.status', $incident), [
            'status' => 'Received', 'notes' => 'Dispatcher reviewed the report.',
        ])->assertRedirect(route('dispatcher.incidents.show', $incident));

        $statusId = DB::table('incident_statuses')->where('name', 'Received')->value('id');
        $this->assertDatabaseHas('incidents', ['id' => $incident->id, 'status_id' => $statusId]);
        $this->assertDatabaseHas('incident_histories', ['incident_id' => $incident->id, 'changed_by' => $dispatcher->id, 'status_id' => $statusId]);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $this->seedReferenceData();
        $incident = $this->createIncident(['status' => 'Reported']);

        $this->actingAs($dispatcher)->patch(route('dispatcher.incidents.status', $incident), ['status' => 'Completed'])
            ->assertStatus(422);
    }

    public function test_non_dispatchers_are_denied_and_completed_incidents_remain_visible(): void
    {
        $this->seedReferenceData();
        $this->createIncident(['status' => 'Completed']);
        $volunteer = $this->userWithRole('volunteer');
        $this->actingAs($volunteer)->get(route('dispatcher.dashboard'))->assertForbidden();
        $this->actingAs($volunteer)->get(route('dispatcher.map'))->assertForbidden();
        $this->actingAs($this->userWithRole('dispatcher'))->get(route('dispatcher.dashboard'))
            ->assertOk()->assertSee('Completed Incidents')->assertSee('1');
    }

    private function userWithRole(string $slug): User
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        return User::factory()->create(['role_id' => Role::where('slug', $slug)->value('id'), 'is_active' => true]);
    }

    private function seedReferenceData(): void
    {
        $this->seed(\Database\Seeders\ReferenceDataSeeder::class);
    }

    private function createIncident(array $overrides = []): Incident
    {
        $category = $overrides['category'] ?? 'Flood';
        $barangay = $overrides['barangay'] ?? 'Lahug';
        $severity = $overrides['severity'] ?? 'Moderate';
        $status = $overrides['status'] ?? 'Reported';
        DB::table('barangays')->updateOrInsert(['name' => $barangay], ['created_at' => now(), 'updated_at' => now()]);
        return Incident::factory()->create([
            ...$overrides,
            'category' => $category, 'barangay' => $barangay, 'severity' => $severity, 'status' => $status,
            'category_id' => DB::table('incident_categories')->where('name', $category)->value('id'),
            'barangay_id' => DB::table('barangays')->where('name', $barangay)->value('id'),
            'severity_id' => DB::table('severity_levels')->where('name', $severity)->value('id'),
            'status_id' => DB::table('incident_statuses')->where('name', $status)->value('id'),
        ]);
    }
}
