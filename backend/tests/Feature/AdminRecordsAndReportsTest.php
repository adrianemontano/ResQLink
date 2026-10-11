<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRecordsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_incident_records(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['category' => 'Flood', 'barangay' => 'Lahug', 'status' => 'Reported']);
        Incident::factory()->create(['category' => 'Fire', 'barangay' => 'Mabolo', 'status' => 'Completed']);

        $this->actingAs($admin)->get(route('admin.incidents.index', [
            'search' => 'Lahug', 'category' => 'Flood', 'status' => 'Reported',
        ]))->assertOk()->assertSee('Lahug')->assertDontSee('Mabolo');
    }

    public function test_admin_search_matches_nearest_landmark_field(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['nearest_landmark' => 'Capitol Fountain', 'barangay' => 'Capitol Site']);
        Incident::factory()->create(['nearest_landmark' => 'Pier Landing', 'barangay' => 'Mabolo']);

        $this->actingAs($admin)->get(route('admin.incidents.index', ['search' => 'Capitol Fountain']))
            ->assertOk()
            ->assertSee('Capitol Fountain')
            ->assertDontSee('Pier Landing');
    }

    public function test_admin_can_view_archived_completed_records(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['status' => 'Reported', 'barangay' => 'Active Area']);
        Incident::factory()->create(['status' => 'Completed', 'barangay' => 'Closed Area']);

        $this->actingAs($admin)->get(route('admin.incidents.index', ['scope' => 'archived']))
            ->assertOk()
            ->assertSee('Closed Area')
            ->assertDontSee('Active Area');
    }

    public function test_admin_active_scope_excludes_archived_records(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['status' => 'Reported', 'barangay' => 'Active Area']);
        Incident::factory()->create(['status' => 'Completed', 'barangay' => 'Closed Area']);

        $this->actingAs($admin)->get(route('admin.incidents.index', ['scope' => 'active']))
            ->assertOk()
            ->assertSee('Active Area')
            ->assertDontSee('Closed Area');
    }

    public function test_record_scope_counts_are_exposed_for_the_switcher(): void
    {
        $admin = $this->admin();
        Incident::factory()->count(2)->create(['status' => 'Reported']);
        Incident::factory()->count(3)->create(['status' => 'Completed']);

        $this->actingAs($admin)->get(route('admin.incidents.index'))
            ->assertOk()
            ->assertSee('Archived / Historical');
    }

    public function test_admin_can_view_read_only_incident_history(): void
    {
        $admin = $this->admin();
        $incident = Incident::factory()->create();

        $this->actingAs($admin)->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee('Read-only administrative incident record.')
            ->assertSee('Status History')
            ->assertDontSee('Update Status');
    }

    public function test_report_period_excludes_incidents_outside_selected_day(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['reported_at' => '2026-08-10 12:00:00', 'category' => 'Flood']);
        Incident::factory()->create(['reported_at' => '2026-08-11 00:00:00', 'category' => 'Fire']);

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily', 'date' => '2026-08-10',
        ]))->assertOk()->assertSee('Total Incidents')->assertSee('1');
    }

    public function test_empty_report_period_has_zero_total(): void
    {
        $this->actingAs($this->admin())->get(route('admin.reports.index', [
            'period' => 'monthly', 'date' => '2030-01-01',
        ]))->assertOk()->assertSee('0')->assertSee('No incident data in this period.');
    }

    public function test_report_shows_status_and_frequency_totals_for_selected_period(): void
    {
        $this->actingAs($this->admin())->get(route('admin.reports.index', [
            'period' => 'daily', 'date' => '2026-08-10',
        ]))->assertOk()
            ->assertSee('Total Incidents')
            ->assertSee('Archived — Completed')
            ->assertSee('Days With Incidents');
    }

    public function test_non_admin_cannot_access_records_or_reports(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $this->actingAs($dispatcher)->get(route('admin.incidents.index'))->assertForbidden();
        $this->actingAs($dispatcher)->get(route('admin.reports.index'))->assertForbidden();
    }

    private function admin(): User
    {
        return $this->userWithRole('admin');
    }

    private function userWithRole(string $slug): User
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        return User::factory()->create([
            'role_id' => Role::where('slug', $slug)->value('id'),
            'is_active' => true,
        ]);
    }
}
