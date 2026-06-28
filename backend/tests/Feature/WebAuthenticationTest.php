<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_admin_is_seeded_with_a_hashed_password(): void
    {
        $this->seed([RoleSeeder::class, AdminSeeder::class]);

        $admin = User::query()->where('username', 'admin')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue(Hash::check('Admin@12345', $admin->password));
        $this->assertNotSame('Admin@12345', $admin->password);
    }

    public function test_admin_can_log_in_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = $this->createUser('admin', ['password' => 'Admin@12345']);

        $response = $this->post('/login', [
            'login' => $admin->username,
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_dispatcher_can_log_in_and_is_redirected_to_dispatcher_dashboard(): void
    {
        $dispatcher = $this->createUser('dispatcher', ['password' => 'Dispatcher@12345']);

        $response = $this->post('/login', [
            'login' => $dispatcher->username,
            'password' => 'Dispatcher@12345',
        ]);

        $response->assertRedirect(route('dispatcher.dashboard'));
        $this->assertAuthenticatedAs($dispatcher);
    }

    public function test_volunteer_cannot_log_in_through_web(): void
    {
        $volunteer = $this->createUser('volunteer', ['password' => 'Volunteer@12345']);

        $response = $this->from('/login')->post('/login', [
            'login' => $volunteer->username,
            'password' => 'Volunteer@12345',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_dispatcher_cannot_access_admin_dashboard(): void
    {
        $dispatcher = $this->createUser('dispatcher');

        $this->actingAs($dispatcher)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_create_dispatcher_account(): void
    {
        $admin = $this->createUser('admin');
        $dispatcherRole = Role::query()->where('slug', 'dispatcher')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Dispatch User',
            'username' => 'dispatch_user',
            'email' => 'dispatch@example.com',
            'role_id' => $dispatcherRole->id,
            'password' => 'Dispatcher@12345',
            'password_confirmation' => 'Dispatcher@12345',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $dispatcher = User::query()->where('username', 'dispatch_user')->firstOrFail();

        $this->assertTrue($dispatcher->hasRole('dispatcher'));
        $this->assertTrue(Hash::check('Dispatcher@12345', $dispatcher->password));
    }

    public function test_deactivated_dispatcher_cannot_log_in(): void
    {
        $dispatcher = $this->createUser('dispatcher', [
            'password' => 'Dispatcher@12345',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'login' => $dispatcher->username,
            'password' => 'Dispatcher@12345',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(string $roleSlug, array $attributes = []): User
    {
        $this->seed(RoleSeeder::class);

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'password' => 'password',
            'is_active' => true,
        ], $attributes));
    }
}
