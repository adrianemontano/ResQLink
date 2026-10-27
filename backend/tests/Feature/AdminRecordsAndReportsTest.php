<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentStatus;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRecordsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_incident_fields_and_reporter_identity(): void
    {
        $admin = $this->admin();
        $reporter = $this->userWithRole('volunteer', [
            'name' => 'Morgan Bay',
            'first_name' => 'Morgan',
            'last_name' => 'Bay',
        ]);
        $incident = Incident::factory()->create([
            'reported_by' => null,
            'volunteer_id' => $reporter->id,
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'nearest_landmark' => 'North Clinic',
            'notes' => 'River level rising',
            'severity' => 'Critical',
            'affected_population' => 42,
            'persons_count' => 42,
            'latitude' => 10.3157,
            'longitude' => 123.8854,
            'impact_radius' => 300,
            'reported_at' => '2026-08-10 14:25:00',
        ]);
        $this->actingAs($admin);

        foreach ([
            'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT),
            'Morgan Bay',
            'North Clinic',
            'River level',
            'Critical',
            '42',
            '10.3157',
            '123.8854',
            '300',
            '2026-08-10',
        ] as $search) {
            $this->get(route('admin.incidents.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT));
        }
    }

    public function test_admin_can_filter_incident_records_by_category_and_status(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['category' => 'Flood', 'barangay' => 'Lahug', 'status' => 'Reported']);
        Incident::factory()->create(['category' => 'Fire', 'barangay' => 'Mabolo', 'status' => 'Completed']);

        $this->actingAs($admin)->get(route('admin.incidents.index', [
            'category' => 'Flood',
            'status' => 'Reported',
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
        $this->assertSame(
            ['all' => 5, 'active' => 2, 'archived' => 3],
            $this->get(route('admin.incidents.index'))->viewData('recordCounts'),
        );
    }

    public function test_admin_incident_search_paginates_and_preserves_query_filters(): void
    {
        $admin = $this->admin();
        Incident::factory()->count(16)->create(['category' => 'Flood', 'barangay' => 'Lahug']);

        $response = $this->actingAs($admin)->get(route('admin.incidents.index', [
            'category' => 'Flood',
            'search' => 'Lahug',
            'page' => 2,
        ]));

        $response->assertOk();
        $this->assertSame(16, $response->viewData('incidents')->total());
        $this->assertSame(1, $response->viewData('incidents')->count());
        $response->assertSee('category=Flood', false)->assertSee('search=Lahug', false);
    }

    public function test_admin_can_view_completed_incident_and_read_only_status_history(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $admin = $this->admin();
        $dispatcher = $this->userWithRole('dispatcher', ['name' => 'Dispatch User']);
        $incident = Incident::factory()->create([
            'status' => 'Completed',
            'reported_at' => '2020-01-02 10:00:00',
        ]);
        $incident->history()->create([
            'status_id' => IncidentStatus::where('name', 'Received')->value('id'),
            'changed_by' => $dispatcher->id,
            'notes' => 'Reviewed by dispatcher',
            'changed_at' => '2020-01-02 11:00:00',
        ]);

        $this->actingAs($admin)->get(route('admin.incidents.index'))
            ->assertOk()
            ->assertSee('INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('Completed');

        $this->actingAs($admin)->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee('Archived / Historical')
            ->assertSee('Completed')
            ->assertSee('Received')
            ->assertSee('Dispatch User')
            ->assertSee('Reviewed by dispatcher')
            ->assertSee('Read-only administrative incident record.')
            ->assertDontSee('Update status')
            ->assertDontSee('Update Status');
    }

    public function test_admin_can_report_daily_totals_and_distributions_from_persisted_incidents(): void
    {
        $admin = $this->admin();
        Incident::factory()->create([
            'reported_at' => '2026-08-10 00:00:00',
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'status' => 'Reported',
        ]);
        Incident::factory()->create([
            'reported_at' => '2026-08-10 23:59:59',
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'status' => 'Received',
        ]);
        Incident::factory()->create([
            'reported_at' => '2026-08-11 00:00:00',
            'category' => 'Fire',
            'barangay' => 'Mabolo',
            'status' => 'Completed',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-08-10',
        ]));

        $response->assertOk()
            ->assertSee('2026-08-10 00:00')
            ->assertSee('2026-08-11 00:00')
            ->assertSee('UTC. Weekly periods run Monday through Sunday.');
        $this->assertSame(2, $response->viewData('totalIncidents'));
        $this->assertSame(2, $response->viewData('byCategory')->first()->total);
        $this->assertSame('Lahug', $response->viewData('byBarangay')->first()->label);
        $this->assertCount(1, $response->viewData('byStatus')->where('label', 'Reported'));
        $this->assertSame([['date' => '2026-08-10', 'total' => 2]], $response->viewData('byFrequency'));
    }

    public function test_weekly_report_uses_monday_to_monday_exclusive_boundaries(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['reported_at' => '2026-08-09 23:59:59']);
        Incident::factory()->create(['reported_at' => '2026-08-10 00:00:00']);
        Incident::factory()->create(['reported_at' => '2026-08-16 23:59:59']);
        Incident::factory()->create(['reported_at' => '2026-08-17 00:00:00']);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'weekly',
            'date' => '2026-08-12',
        ]));

        $response->assertOk()->assertSee('Weekly periods run Monday through Sunday.');
        $this->assertSame(2, $response->viewData('totalIncidents'));
        $this->assertCount(7, $response->viewData('byFrequency'));
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

    public function test_monthly_report_includes_full_month_and_excludes_neighboring_dates(): void
    {
        $admin = $this->admin();
        Incident::factory()->create(['reported_at' => '2026-07-31 23:59:59']);
        Incident::factory()->create(['reported_at' => '2026-08-01 00:00:00']);
        Incident::factory()->create(['reported_at' => '2026-08-31 23:59:59']);
        Incident::factory()->create(['reported_at' => '2026-09-01 00:00:00']);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'monthly',
            'date' => '2026-08-01',
        ]));

        $response->assertOk()
            ->assertSee('2026-08-01 00:00')
            ->assertSee('2026-09-01 00:00')
            ->assertSee('UTC. Weekly periods run Monday through Sunday.');
        $this->assertSame(2, $response->viewData('totalIncidents'));
        $this->assertCount(31, $response->viewData('byFrequency'));
    }

    public function test_legacy_incidents_without_reported_at_use_creation_time_in_reports(): void
    {
        $admin = $this->admin();
        $incident = Incident::factory()->create(['created_at' => '2026-08-10 12:00:00']);
        DB::table('incidents')->where('id', $incident->id)->update(['reported_at' => null]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'period' => 'daily',
            'date' => '2026-08-10',
        ]));

        $this->assertSame(1, $response->viewData('totalIncidents'));
    }

    public function test_empty_report_period_returns_zeroes_and_an_explicit_empty_state(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.reports.index', [
            'period' => 'monthly',
            'date' => '2030-01-01',
        ]));

        $response->assertOk()
            ->assertSee('0')
            ->assertSee('No incident data in this period. All totals are zero.')
            ->assertSee('2030-01-31: 0');
        $this->assertSame(0, $response->viewData('totalIncidents'));
        $this->assertFalse($response->viewData('hasIncidents'));
        $this->assertCount(31, $response->viewData('byFrequency'));
        $this->assertSame(0, $response->viewData('byFrequency')[30]['total']);
    }

    public function test_dispatcher_and_volunteer_are_denied_every_admin_record_and_report_route(): void
    {
        $incident = Incident::factory()->create();

        foreach (['dispatcher', 'volunteer'] as $role) {
            $user = $this->userWithRole($role);
            $this->actingAs($user)->get(route('admin.incidents.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.incidents.show', $incident))->assertForbidden();
            $this->actingAs($user)->get(route('admin.reports.index'))->assertForbidden();
        }
    }

    private function admin(): User
    {
        return $this->userWithRole('admin');
    }

    /** @param array<string, mixed> $attributes */
    private function userWithRole(string $slug, array $attributes = []): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            ...$attributes,
            'role_id' => Role::where('slug', $slug)->value('id'),
            'is_active' => true,
        ]);

        if ($slug === 'volunteer') {
            $user->volunteerProfile()->create([
                'barangay' => 'Lahug',
                'verification_status' => 'verified',
            ]);
        }

        return $user;
    }
}
