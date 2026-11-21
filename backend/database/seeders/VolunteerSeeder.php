<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VolunteerSeeder extends Seeder
{
    private const DEFAULT_USERNAME = 'volunteer';
    private const DEFAULT_PASSWORD = 'Volunteer@12345';
    private const DEFAULT_EMAIL = 'volunteer@resqlink.local';
    private const DEFAULT_NAME = 'Barangay Responder';
    private const DEFAULT_FIRST_NAME = 'Community';
    private const DEFAULT_LAST_NAME = 'Volunteer';
    private const DEFAULT_BARANGAY = 'Lahug';

    public function run(): void
    {
        $volunteerRole = Role::query()->where('name', 'volunteer')->firstOrFail();

        $userAttributes = [
            'username' => env('DEFAULT_VOLUNTEER_USERNAME', self::DEFAULT_USERNAME),
            'password' => bcrypt(env('DEFAULT_VOLUNTEER_PASSWORD', self::DEFAULT_PASSWORD)),
            'email' => env('DEFAULT_VOLUNTEER_EMAIL', self::DEFAULT_EMAIL),
            'role_id' => $volunteerRole->id,
            'is_active' => true,
        ];

        if (Schema::hasColumn('users', 'name')) {
            $userAttributes['name'] = env('DEFAULT_VOLUNTEER_NAME', self::DEFAULT_NAME);
        } else {
            $userAttributes['first_name'] = env('DEFAULT_VOLUNTEER_FIRST_NAME', self::DEFAULT_FIRST_NAME);
            $userAttributes['last_name'] = env('DEFAULT_VOLUNTEER_LAST_NAME', self::DEFAULT_LAST_NAME);
        }

        $volunteer = User::query()->updateOrCreate(
            ['email' => $userAttributes['email']],
            $userAttributes,
        );

        $volunteer->volunteerProfile()->updateOrCreate(
            ['user_id' => $volunteer->id],
            [
                'barangay' => env('DEFAULT_VOLUNTEER_BARANGAY', self::DEFAULT_BARANGAY),
                'verification_status' => 'verified',
                'verified_at' => now(),
            ],
        );
    }
}
