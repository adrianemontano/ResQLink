<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_volunteer_can_login_restore_session_and_logout(): void
    {
        $volunteer = $this->verifiedVolunteer();

        $login = $this->postJson(route('api.mobile.login'), [
            'login' => $volunteer->username,
            'password' => 'Volunteer@12345',
            'device_name' => 'Test Android',
        ]);

        $token = $login->assertOk()
            ->assertJsonPath('data.user.id', $volunteer->id)
            ->assertJsonPath('data.user.role', 'volunteer')
            ->json('data.token');

        $this->withToken($token)->getJson(route('api.mobile.user'))
            ->assertOk()
            ->assertJsonPath('data.id', $volunteer->id)
            ->assertJsonPath('data.barangay', 'Lahug');

        $this->withToken($token)->postJson(route('api.mobile.logout'))->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        auth()->forgetGuards();
        $this->withToken($token)->getJson(route('api.mobile.user'))->assertUnauthorized();
    }

    public function test_mobile_login_rejects_inactive_unverified_and_non_volunteer_users(): void
    {
        $inactive = $this->verifiedVolunteer(['is_active' => false]);
        $unverified = $this->verifiedVolunteer([], 'pending');
        $admin = $this->userWithRole('admin');

        foreach ([$inactive, $unverified] as $user) {
            $this->postJson(route('api.mobile.login'), $this->credentials($user))
                ->assertUnprocessable();
        }

        $this->postJson(route('api.mobile.login'), $this->credentials($admin))
            ->assertForbidden();
    }

    public function test_temporary_password_blocks_incidents_but_allows_password_change(): void
    {
        $this->seed(ReferenceDataSeeder::class);
        $volunteer = $this->verifiedVolunteer(['must_change_password' => true]);
        $token = $volunteer->createToken(
            'Test Android',
            ['password:update', 'incidents:create'],
            now()->addDays(30),
        )->plainTextToken;

        $this->withToken($token)->postJson(route('api.incidents.store'), $this->incidentData())
            ->assertForbidden()
            ->assertJsonPath('must_change_password', true);

        $this->withToken($token)->putJson(route('api.mobile.password.update'), [
            'current_password' => 'Volunteer@12345',
            'password' => 'NewVolunteer@12345',
            'password_confirmation' => 'NewVolunteer@12345',
        ])->assertOk()->assertJsonPath('must_change_password', false);

        $this->withToken($token)->postJson(route('api.incidents.store'), $this->incidentData())
            ->assertCreated()
            ->assertJsonPath('data.status', 'Reported');
    }

    public function test_expired_token_cannot_authenticate(): void
    {
        $volunteer = $this->verifiedVolunteer();
        $token = $volunteer->createToken('Expired', ['profile:read'], now()->subMinute())->plainTextToken;

        $this->withToken($token)->getJson(route('api.mobile.user'))->assertUnauthorized();
    }

    private function verifiedVolunteer(array $attributes = [], string $verification = 'verified'): User
    {
        $volunteer = $this->userWithRole('volunteer', $attributes);
        $volunteer->volunteerProfile()->create([
            'barangay' => 'Lahug',
            'verification_status' => $verification,
            'verified_at' => $verification === 'verified' ? now() : null,
        ]);

        return $volunteer;
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create([
            'role_id' => Role::query()->where('slug', $role)->value('id'),
            'password' => 'Volunteer@12345',
            ...$attributes,
        ]);
    }

    private function credentials(User $user): array
    {
        return ['login' => $user->username, 'password' => 'Volunteer@12345'];
    }

    private function incidentData(): array
    {
        return [
            'category' => 'Flood',
            'barangay' => 'Lahug',
            'nearest_landmark' => 'Barangay Hall',
            'affected_population' => 25,
            'latitude' => 10.3302,
            'longitude' => 123.8988,
            'impact_radius' => 300,
            'notes' => 'Water is rising.',
        ];
    }
}
