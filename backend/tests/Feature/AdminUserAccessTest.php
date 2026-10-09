<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\VolunteerDocument;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_volunteer_document_to_private_storage(): void
    {
        Storage::fake(VolunteerDocument::DISK);
        $admin = $this->createUser('admin');
        $volunteer = $this->createVolunteer();

        $this->actingAs($admin)->post(route('admin.users.documents.store', $volunteer), [
            'document_type' => 'barangay_clearance',
            'document' => UploadedFile::fake()->create('clearance.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $document = VolunteerDocument::query()->firstOrFail();

        $this->assertSame('pending', $document->review_status);
        $this->assertSame('clearance.pdf', $document->original_filename);
        $this->assertSame($volunteer->id, (int) $document->volunteer_profile_id);
        Storage::disk(VolunteerDocument::DISK)->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_duplicate_document_type_and_bad_file_return_field_errors(): void
    {
        Storage::fake(VolunteerDocument::DISK);
        $admin = $this->createUser('admin');
        $volunteer = $this->createVolunteer();
        $route = route('admin.users.documents.store', $volunteer);

        $this->actingAs($admin)->post($route, [
            'document_type' => 'endorsement_letter',
            'document' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ]);
        $this->actingAs($admin)->post($route, [
            'document_type' => 'endorsement_letter',
            'document' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('document_type');
        $this->actingAs($admin)->post($route, [
            'document_type' => 'barangay_clearance',
            'document' => UploadedFile::fake()->create('script.exe', 10),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('volunteer_documents', 1);
    }

    public function test_only_admin_can_download_and_review_documents_of_the_matching_volunteer(): void
    {
        Storage::fake(VolunteerDocument::DISK);
        $admin = $this->createUser('admin');
        $volunteer = $this->createVolunteer();
        $otherVolunteer = $this->createVolunteer();
        $this->actingAs($admin)->post(route('admin.users.documents.store', $volunteer), [
            'document_type' => 'certificate_of_residency',
            'document' => UploadedFile::fake()->create('residency.pdf', 10, 'application/pdf'),
        ]);
        $document = VolunteerDocument::query()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.users.documents.show', [$volunteer, $document]))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.documents.show', [$otherVolunteer, $document]))->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.users.documents.review', [$volunteer, $document]), ['review_status' => 'approved'])
            ->assertSessionHasNoErrors();
        $this->assertSame('approved', $document->fresh()->review_status);
        $this->assertSame($admin->id, (int) $document->fresh()->reviewed_by);

        $this->actingAs($this->createUser('dispatcher'))
            ->get(route('admin.users.documents.show', [$volunteer, $document]))->assertForbidden();
        $this->actingAs($volunteer)
            ->patch(route('admin.users.documents.review', [$volunteer, $document]), ['review_status' => 'approved'])
            ->assertForbidden();
    }

    public function test_inactive_or_unverified_volunteers_are_rejected_on_direct_requests(): void
    {
        $inactive = $this->createVolunteer(['is_active' => false]);
        $unverified = $this->createVolunteer([], 'pending');

        foreach ([$inactive, $unverified] as $volunteer) {
            $this->actingAs($volunteer)->get(route('volunteer.dashboard'))->assertForbidden();
            $this->actingAs($volunteer)->get(route('volunteer.incidents.create'))->assertForbidden();
            $this->actingAs($volunteer)->post(route('volunteer.incidents.store'), [])->assertForbidden();
            $this->actingAs($volunteer)->postJson('/api/incidents', [])->assertForbidden();
        }
    }

    public function test_duplicate_account_details_and_password_mismatch_show_field_errors(): void
    {
        $admin = $this->createUser('admin');
        $existing = $this->createUser('dispatcher');

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Another User',
            'username' => $existing->username,
            'email' => $existing->email,
            'role_id' => $existing->role_id,
            'password' => 'Dispatcher@12345',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['username', 'email', 'password']);
    }

    public function test_changing_role_creates_and_removes_volunteer_profile(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser('dispatcher');
        $volunteerRoleId = Role::query()->where('name', 'volunteer')->value('id');
        $fields = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('admin.users.update', $user), $fields + [
            'role_id' => $volunteerRoleId,
            'barangay' => 'Lahug',
        ]);
        $this->assertDatabaseHas('volunteer_profiles', ['user_id' => $user->id, 'verification_status' => 'pending']);

        $this->actingAs($admin)->put(route('admin.users.update', $user), $fields + ['role_id' => $user->role_id]);
        $this->assertDatabaseMissing('volunteer_profiles', ['user_id' => $user->id]);
    }

    public function test_admin_cannot_deactivate_or_demote_own_account(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->patch(route('admin.users.activation', $admin))->assertSessionHasErrors('account');
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'role_id' => Role::query()->where('name', 'dispatcher')->value('id'),
            'is_active' => '1',
        ])->assertSessionHasErrors('account');

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    /** @param array<string, mixed> $attributes */
    private function createUser(string $roleName, array $attributes = []): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create(array_merge([
            'role_id' => Role::query()->where('name', $roleName)->value('id'),
        ], $attributes));
    }

    /** @param array<string, mixed> $attributes */
    private function createVolunteer(array $attributes = [], string $verification = 'verified'): User
    {
        $volunteer = $this->createUser('volunteer', $attributes);
        $volunteer->volunteerProfile()->create(['barangay' => 'Lahug', 'verification_status' => $verification]);

        return $volunteer;
    }
}
