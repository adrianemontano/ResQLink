<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class VolunteerSeeder extends Seeder
{
    /**
     * Seed the default volunteer account and pending profile.
     */
    public function run(): void
    {
        $volunteerRole = Role::query()->where('name', 'volunteer')->firstOrFail();

        $volunteer = User::query()->updateOrCreate(
            ['email' => env('DEFAULT_VOLUNTEER_EMAIL', 'volunteer@resqlink.local')],
            [
                'first_name' => env('DEFAULT_VOLUNTEER_FIRST_NAME', 'Community'),
                'last_name' => env('DEFAULT_VOLUNTEER_LAST_NAME', 'Volunteer'),
                'username' => env('DEFAULT_VOLUNTEER_USERNAME', 'volunteer'),
                'password' => env('DEFAULT_VOLUNTEER_PASSWORD', 'Volunteer@12345'),
                'role_id' => $volunteerRole->id,
                'is_active' => true,
            ],
        );

        $volunteer->volunteerProfile()->updateOrCreate(
            ['user_id' => $volunteer->id],
            [
                'barangay' => env('DEFAULT_VOLUNTEER_BARANGAY', 'Lahug'),
                'verification_status' => 'pending',
                'verified_at' => null,
            ],
        );
    }
}
