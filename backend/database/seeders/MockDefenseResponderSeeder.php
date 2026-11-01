<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\VolunteerProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MockDefenseResponderSeeder extends Seeder
{
    /**
     * Seed at least 8 verified Barangay Responders for mock defense.
     */
    public function run(): void
    {
        $volunteerRole = Role::query()->where('name', 'volunteer')->firstOrFail();

        $responders = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'username' => 'responder_mabolo',
                'email' => 'responder.mabolo@resqlink.local',
                'contact_number' => '09171010001',
                'barangay' => 'Mabolo',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'username' => 'responder_guadalupe',
                'email' => 'responder.guadalupe@resqlink.local',
                'contact_number' => '09171010002',
                'barangay' => 'Guadalupe',
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Garcia',
                'username' => 'responder_capitol',
                'email' => 'responder.capitol@resqlink.local',
                'contact_number' => '09171010003',
                'barangay' => 'Capitol Site',
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'username' => 'responder_talamban',
                'email' => 'responder.talamban@resqlink.local',
                'contact_number' => '09171010004',
                'barangay' => 'Talamban',
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Mendoza',
                'username' => 'responder_banilad',
                'email' => 'responder.banilad@resqlink.local',
                'contact_number' => '09171010005',
                'barangay' => 'Banilad',
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Bautista',
                'username' => 'responder_tisa',
                'email' => 'responder.tisa@resqlink.local',
                'contact_number' => '09171010006',
                'barangay' => 'Tisa',
            ],
            [
                'first_name' => 'Roberto',
                'last_name' => 'Lim',
                'username' => 'responder_pardo',
                'email' => 'responder.pardo@resqlink.local',
                'contact_number' => '09171010007',
                'barangay' => 'Poblacion Pardo',
            ],
            [
                'first_name' => 'Grace',
                'last_name' => 'Tan',
                'username' => 'responder_kasambagan',
                'email' => 'responder.kasambagan@resqlink.local',
                'contact_number' => '09171010008',
                'barangay' => 'Kasambagan',
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Villanueva',
                'username' => 'responder_sambag',
                'email' => 'responder.sambag@resqlink.local',
                'contact_number' => '09171010009',
                'barangay' => 'Sambag I',
            ],
            [
                'first_name' => 'Lourdes',
                'last_name' => 'Castro',
                'username' => 'responder_lahug',
                'email' => 'responder.lahug@resqlink.local',
                'contact_number' => '09171010010',
                'barangay' => 'Lahug',
            ],
        ];

        foreach ($responders as $data) {
            DB::table('barangays')->updateOrInsert(
                ['name' => $data['barangay']],
                ['created_at' => now(), 'updated_at' => now()],
            );

            $attributes = [
                'username' => $data['username'],
                'password' => Hash::make('Volunteer@12345'),
                'role_id' => $volunteerRole->id,
                'is_active' => true,
            ];

            if (Schema::hasColumn('users', 'first_name')) {
                $attributes['first_name'] = $data['first_name'];
                $attributes['last_name'] = $data['last_name'];
            }
            if (Schema::hasColumn('users', 'contact_number')) {
                $attributes['contact_number'] = $data['contact_number'];
            }
            if (Schema::hasColumn('users', 'name')) {
                $attributes['name'] = "{$data['first_name']} {$data['last_name']}";
            }

            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                $attributes,
            );

            VolunteerProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'barangay' => $data['barangay'],
                    'verification_status' => 'verified',
                    'verified_at' => now()->subDays(5),
                ],
            );
        }
    }
}
