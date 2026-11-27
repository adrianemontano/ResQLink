<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentDispatch;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IncidentDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, ReferenceDataSeeder::class]);
    }

    public function test_moving_to_dispatched_requires_and_records_a_team(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $incident = $this->createIncident('Received');
        $team = $this->createTeam('Lahug Team');

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.status', $incident), ['status' => 'Dispatched', 'notes' => 'Sending help.'])
            ->assertSessionHasErrors('team_id');
        $this->assertSame('Received', $incident->fresh()->status);

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.status', $incident), [
                'status' => 'Dispatched', 'notes' => 'Sending help.', 'team_id' => $team->id,
            ])
            ->assertRedirect(route('dispatcher.incidents.show', $incident));

        $this->assertSame('Dispatched', $incident->fresh()->status);
        $this->assertDatabaseHas('incident_dispatches', [
            'incident_id' => $incident->id, 'team_id' => $team->id, 'dispatched_by' => $dispatcher->id, 'cancelled_at' => null,
        ]);
    }

    public function test_dispatcher_can_send_another_team_but_not_the_same_team_twice(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $incident = $this->createIncident('Dispatched');
        $first = $this->createTeam('First Team');
        $second = $this->createTeam('Second Team');
        $this->dispatch($incident, $first, $dispatcher);

        $this->actingAs($dispatcher)
            ->post(route('dispatcher.incidents.dispatches.store', $incident), ['team_id' => $first->id])
            ->assertSessionHasErrors('team_id');

        $this->actingAs($dispatcher)
            ->post(route('dispatcher.incidents.dispatches.store', $incident), ['team_id' => $second->id, 'notes' => 'Water rising.'])
            ->assertRedirect(route('dispatcher.incidents.show', $incident));

        $this->assertSame(2, $incident->dispatches()->active()->count());
        $this->assertDatabaseHas('incident_histories', ['incident_id' => $incident->id, 'entry_type' => 'update', 'category' => 'Coordination']);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.show', $incident))
            ->assertOk()->assertSee('First Team')->assertSee('Second Team');
    }

    public function test_team_on_an_open_incident_is_hidden_and_rejected_until_that_incident_is_completed(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $busyIncident = $this->createIncident('Dispatched');
        $otherIncident = $this->createIncident('Received');
        $busyTeam = $this->createTeam('Busy Team');
        $this->createTeam('Free Team');
        $this->dispatch($busyIncident, $busyTeam, $dispatcher);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.show', $otherIncident))
            ->assertOk()->assertSee('Free Team')->assertDontSee('Busy Team');

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.status', $otherIncident), [
                'status' => 'Dispatched', 'notes' => 'Sending help.', 'team_id' => $busyTeam->id,
            ])
            ->assertSessionHasErrors('team_id');
        $this->assertSame('Received', $otherIncident->fresh()->status);

        $busyIncident->update(['status' => 'Completed']);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.show', $otherIncident))
            ->assertOk()->assertSee('Busy Team');
    }

    public function test_another_team_cannot_be_sent_before_the_incident_is_dispatched(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $incident = $this->createIncident('Received');

        $this->actingAs($dispatcher)
            ->post(route('dispatcher.incidents.dispatches.store', $incident), ['team_id' => $this->createTeam('Early Team')->id])
            ->assertSessionHasErrors('team_id');

        $this->assertSame(0, $incident->dispatches()->count());
    }

    public function test_cancelling_the_last_active_dispatch_returns_incident_to_received(): void
    {
        $dispatcher = $this->userWithRole('dispatcher');
        $incident = $this->createIncident('Dispatched');
        $first = $this->dispatch($incident, $this->createTeam('First Team'), $dispatcher);
        $second = $this->dispatch($incident, $this->createTeam('Second Team'), $dispatcher);

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.dispatches.cancellation', [$incident, $first]), [])
            ->assertSessionHasErrors('cancellation_reason');

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.dispatches.cancellation', [$incident, $first]), ['cancellation_reason' => 'Road blocked.']);
        $this->assertNotNull($first->fresh()->cancelled_at);
        $this->assertSame('Dispatched', $incident->fresh()->status);

        $this->actingAs($dispatcher)
            ->patch(route('dispatcher.incidents.dispatches.cancellation', [$incident, $second]), ['cancellation_reason' => 'Recalled.']);
        $this->assertSame('Received', $incident->fresh()->status);
    }

    public function test_admin_sees_dispatches_read_only_and_in_the_team_report(): void
    {
        $admin = $this->userWithRole('admin');
        $incident = $this->createIncident('Dispatched');
        $this->dispatch($incident, $this->createTeam('Report Team'), $this->userWithRole('dispatcher'));

        $this->actingAs($admin)->get(route('admin.incidents.show', $incident))
            ->assertOk()->assertSee('Report Team')->assertDontSee('Dispatch another team');

        $this->actingAs($admin)->get(route('admin.reports.index', ['period' => 'daily', 'date' => now()->toDateString()]))
            ->assertOk()->assertSee('Dispatches by team')->assertSee('Report Team');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Team dispatch frequency')->assertSee('Report Team')->assertSee('On a call');

        $this->actingAs($admin)
            ->post(route('dispatcher.incidents.dispatches.store', $incident), ['team_id' => Team::query()->value('id')])
            ->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::query()->where('name', $roleName)->value('id'),
            'is_active' => true,
        ]);
    }

    private function createTeam(string $name): Team
    {
        $lead = $this->userWithRole('volunteer');
        $team = Team::query()->create(['name' => $name, 'team_lead_id' => $lead->id, 'is_active' => true]);
        $team->members()->attach($lead->id);

        return $team;
    }

    private function dispatch(Incident $incident, Team $team, User $dispatcher): IncidentDispatch
    {
        return $incident->dispatches()->create([
            'team_id' => $team->id, 'dispatched_by' => $dispatcher->id, 'dispatched_at' => now(),
        ]);
    }

    private function createIncident(string $status): Incident
    {
        DB::table('barangays')->updateOrInsert(['name' => 'Lahug'], ['created_at' => now(), 'updated_at' => now()]);

        return Incident::factory()->create([
            'category' => 'Flood', 'barangay' => 'Lahug', 'severity' => 'Moderate', 'status' => $status,
            'category_id' => DB::table('incident_categories')->where('name', 'Flood')->value('id'),
            'barangay_id' => DB::table('barangays')->where('name', 'Lahug')->value('id'),
            'severity_id' => DB::table('severity_levels')->where('name', 'Moderate')->value('id'),
            'status_id' => DB::table('incident_statuses')->where('name', $status)->value('id'),
        ]);
    }
}
