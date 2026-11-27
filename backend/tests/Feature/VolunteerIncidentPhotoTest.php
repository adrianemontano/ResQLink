<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VolunteerIncidentPhotoTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, ReferenceDataSeeder::class]);
        Storage::fake('local');
    }

    public function test_volunteer_form_offers_camera_capture(): void
    {
        $this->actingAs($this->verifiedVolunteer())
            ->get(route('volunteer.incidents.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('capture="environment"', false)
            ->assertSee('Take photo');
    }

    public function test_photo_taken_by_volunteer_is_shown_to_the_dispatcher(): void
    {
        $this->actingAs($this->verifiedVolunteer())
            ->post(route('volunteer.incidents.store'), $this->incidentData([
                // A real 1×1 PNG, so the test does not need the GD extension.
                'photo' => UploadedFile::fake()->createWithContent('incident.png', base64_decode(self::TINY_PNG)),
            ]))
            ->assertRedirect(route('volunteer.incidents.create'))
            ->assertSessionHasNoErrors();

        $incident = Incident::query()->with('photos')->latest('id')->firstOrFail();
        $photo = $incident->photos->first();
        $this->assertNotNull($photo);
        Storage::disk('local')->assertExists($photo->file_path);

        $dispatcher = $this->userWithRole('dispatcher');
        $photoUrl = route('dispatcher.incidents.photo.show', [$incident, $photo]);

        $this->actingAs($dispatcher)->get(route('dispatcher.incidents.show', $incident))
            ->assertOk()
            ->assertSee($photoUrl, false);
        $this->actingAs($dispatcher)->get($photoUrl)->assertOk();
    }

    public function test_non_image_upload_is_rejected(): void
    {
        $this->actingAs($this->verifiedVolunteer())
            ->post(route('volunteer.incidents.store'), $this->incidentData([
                'photo' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('incidents', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function incidentData(array $overrides = []): array
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
            ...$overrides,
        ];
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::query()->where('name', $roleName)->value('id'),
            'is_active' => true,
        ]);
    }

    private function verifiedVolunteer(): User
    {
        $volunteer = $this->userWithRole('volunteer');
        $volunteer->volunteerProfile()->create([
            'barangay' => 'Lahug',
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        return $volunteer;
    }
}
