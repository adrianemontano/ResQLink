<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_creates_team_with_members_and_team_lead(): void
    {
        $admin = $this->createUser('admin');
        [$lead, $member] = [$this->createResponder(), $this->createResponder()];

        $this->actingAs($admin)
            ->post(route('admin.teams.store'), [
                'name' => 'Mabolo Rescue Team',
                'barangay' => 'Mabolo',
                'member_ids' => [$lead->id, $member->id],
                'team_lead_id' => $lead->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.teams.index'));

        $team = Team::query()->where('name', 'Mabolo Rescue Team')->firstOrFail();

        $this->assertTrue($team->teamLead->is($lead));
        $this->assertEqualsCanonicalizing([$lead->id, $member->id], $team->members->pluck('id')->all());
        $this->assertTrue($team->is_active);

        $this->actingAs($admin)->get(route('admin.teams.index'))
            ->assertOk()
            ->assertSee('Mabolo Rescue Team')
            ->assertSee($lead->name);
    }

    public function test_team_lead_must_be_a_selected_member(): void
    {
        $admin = $this->createUser('admin');
        [$member, $outsider] = [$this->createResponder(), $this->createResponder()];

        $this->actingAs($admin)
            ->post(route('admin.teams.store'), [
                'name' => 'Lahug Team',
                'member_ids' => [$member->id],
                'team_lead_id' => $outsider->id,
            ])
            ->assertSessionHasErrors('team_lead_id');

        $this->assertDatabaseCount('teams', 0);
    }

    public function test_only_barangay_responders_can_be_members(): void
    {
        $admin = $this->createUser('admin');
        $dispatcher = $this->createUser('dispatcher');

        $this->actingAs($admin)
            ->post(route('admin.teams.store'), [
                'name' => 'Mixed Team',
                'member_ids' => [$dispatcher->id],
                'team_lead_id' => $dispatcher->id,
            ])
            ->assertSessionHasErrors('member_ids.0');
    }

    public function test_responder_cannot_join_a_second_team_but_can_stay_in_their_own(): void
    {
        $admin = $this->createUser('admin');
        $responder = $this->createResponder();
        $team = $this->createTeam('Talamban Team', $responder);

        $this->actingAs($admin)
            ->post(route('admin.teams.store'), [
                'name' => 'Second Team',
                'member_ids' => [$responder->id],
                'team_lead_id' => $responder->id,
            ])
            ->assertSessionHasErrors('member_ids.0');

        $newMember = $this->createResponder();

        $this->actingAs($admin)
            ->put(route('admin.teams.update', $team), [
                'name' => 'Talamban Team',
                'member_ids' => [$responder->id, $newMember->id],
                'team_lead_id' => $newMember->id,
                'is_active' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.teams.index'));

        $this->assertSame($newMember->id, $team->fresh()->team_lead_id);
        $this->assertCount(2, $team->fresh()->members);
    }

    public function test_admin_can_toggle_team_activation(): void
    {
        $admin = $this->createUser('admin');
        $team = $this->createTeam('Guadalupe Team', $this->createResponder());

        $this->actingAs($admin)
            ->patch(route('admin.teams.activation', $team))
            ->assertRedirect(route('admin.teams.index'));

        $this->assertFalse($team->fresh()->is_active);
    }

    public function test_non_admins_are_denied_team_management(): void
    {
        $team = $this->createTeam('Pardo Team', $this->createResponder());

        foreach (['dispatcher', 'volunteer'] as $roleName) {
            $user = $roleName === 'volunteer' ? $this->createResponder() : $this->createUser($roleName);

            $this->actingAs($user)->get(route('admin.teams.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.teams.edit', $team))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.teams.activation', $team))->assertForbidden();
        }
    }

    private function createUser(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::query()->where('name', $roleName)->value('id'),
            'is_active' => true,
        ]);
    }

    private function createResponder(): User
    {
        $responder = $this->createUser('volunteer');
        $responder->volunteerProfile()->create([
            'barangay' => 'Lahug',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        return $responder;
    }

    private function createTeam(string $name, User $lead): Team
    {
        $team = Team::query()->create(['name' => $name, 'team_lead_id' => $lead->id, 'is_active' => true]);
        $team->members()->attach($lead->id);

        return $team;
    }
}
