<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VolunteerSeeder extends Seeder
{
    /**
     * Seed the default volunteer account and verified development profile.
     */
    public function run(): void
    {
        $volunteerRole = Role::query()->where('name', 'volunteer')->firstOrFail();
        $attributes = [
            'username' => env('DEFAULT_VOLUNTEER_USERNAME', 'volunteer'),
            'password' => env('DEFAULT_VOLUNTEER_PASSWORD', 'Volunteer@12345'),
            'role_id' => $volunteerRole->id,
            'is_active' => true,
        ];

        if (Schema::hasColumn('users', 'name')) {
            $attributes['name'] = env('DEFAULT_VOLUNTEER_NAME', 'Community Volunteer');
        } else {
            $attributes['first_name'] = env('DEFAULT_VOLUNTEER_FIRST_NAME', 'Community');
            $attributes['last_name'] = env('DEFAULT_VOLUNTEER_LAST_NAME', 'Volunteer');
        }

        $volunteer = User::query()->updateOrCreate(
            ['email' => env('DEFAULT_VOLUNTEER_EMAIL', 'volunteer@resqlink.local')],
            $attributes,
        );

        $volunteer->volunteerProfile()->updateOrCreate(
            ['user_id' => $volunteer->id],
            [
                'barangay' => env('DEFAULT_VOLUNTEER_BARANGAY', 'Lahug'),
                'verification_status' => 'verified',
                'verified_at' => now(),
            ],
        );
    }
}
