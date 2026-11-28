<?php

namespace Database\Seeders;

use App\Models\ResponseTeam;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResponseTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $teams = [
            // Search & Rescue teams (4)
            ['name' => 'Alpha Rescue Team', 'code' => 'TEAM-ALPHA', 'type' => 'Search & Rescue', 'contact_number' => '0917-100-0001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'Bravo SAR Unit', 'code' => 'TEAM-BRAVO-SAR', 'type' => 'Search & Rescue', 'contact_number' => '0917-100-0002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'Charlie Rescue Squad', 'code' => 'TEAM-CHARLIE-RES', 'type' => 'Search & Rescue', 'contact_number' => '0917-100-0003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'Delta Emergency Response', 'code' => 'TEAM-DELTA-ER', 'type' => 'Search & Rescue', 'contact_number' => '0917-100-0004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],

            // Medical / EMS teams (4)
            ['name' => 'Bravo Medical / EMS', 'code' => 'TEAM-BRAVO', 'type' => 'Medical / EMS', 'contact_number' => '0917-100-1001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'Mike Medical Unit', 'code' => 'TEAM-MIKE-MED', 'type' => 'Medical / EMS', 'contact_number' => '0917-100-1002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'November EMS', 'code' => 'TEAM-NOV-EMS', 'type' => 'Medical / EMS', 'contact_number' => '0917-100-1003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'Oscar Medical Team', 'code' => 'TEAM-OSCAR-MED', 'type' => 'Medical / EMS', 'contact_number' => '0917-100-1004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],

            // Fire Suppression teams (4)
            ['name' => 'Charlie Fire Response', 'code' => 'TEAM-CHARLIE', 'type' => 'Fire Suppression', 'contact_number' => '0917-100-2001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'Xray Fire Unit', 'code' => 'TEAM-XRAY-FIRE', 'type' => 'Fire Suppression', 'contact_number' => '0917-100-2002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'Yankee Fire Team', 'code' => 'TEAM-YANKEE-FIRE', 'type' => 'Fire Suppression', 'contact_number' => '0917-100-2003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'Zulu Fire Response', 'code' => 'TEAM-ZULU-FIRE', 'type' => 'Fire Suppression', 'contact_number' => '0917-100-2004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],

            // Flood Response teams (4)
            ['name' => 'Delta Flood & Swiftwater', 'code' => 'TEAM-DELTA', 'type' => 'Flood Response', 'contact_number' => '0917-100-3001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'November Flood Team', 'code' => 'TEAM-NOV-FLOOD', 'type' => 'Flood Response', 'contact_number' => '0917-100-3002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'Oscar Swiftwater', 'code' => 'TEAM-OSCAR-SW', 'type' => 'Flood Response', 'contact_number' => '0917-100-3003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'Papa Flood Unit', 'code' => 'TEAM-PAPA-FLOOD', 'type' => 'Flood Response', 'contact_number' => '0917-100-3004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],

            // Rapid Assessment teams (4)
            ['name' => 'Echo Rapid Assessment', 'code' => 'TEAM-ECHO', 'type' => 'Rapid Assessment', 'contact_number' => '0917-100-4001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'Sierra Assessment', 'code' => 'TEAM-SIERRA-RA', 'type' => 'Rapid Assessment', 'contact_number' => '0917-100-4002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'Tango Rapid Team', 'code' => 'TEAM-TANGO-RA', 'type' => 'Rapid Assessment', 'contact_number' => '0917-100-4003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'Uniform Assessment', 'code' => 'TEAM-UNI-RA', 'type' => 'Rapid Assessment', 'contact_number' => '0917-100-4004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],

            // Logistics teams (4)
            ['name' => 'Foxtrot Logistics', 'code' => 'TEAM-FOXTROT-LOG', 'type' => 'Logistics', 'contact_number' => '0917-100-5001', 'barangay' => 'Poblacion', 'latitude' => 10.3172, 'longitude' => 123.9053, 'is_active' => true],
            ['name' => 'Golf Logistics Unit', 'code' => 'TEAM-GOLF-LOG', 'type' => 'Logistics', 'contact_number' => '0917-100-5002', 'barangay' => 'Mabini', 'latitude' => 10.3200, 'longitude' => 123.9000, 'is_active' => true],
            ['name' => 'Hotel Logistics', 'code' => 'TEAM-HOTEL-LOG', 'type' => 'Logistics', 'contact_number' => '0917-100-5003', 'barangay' => 'Loay', 'latitude' => 10.3250, 'longitude' => 123.8950, 'is_active' => true],
            ['name' => 'India Supply Team', 'code' => 'TEAM-INDIA-SUP', 'type' => 'Logistics', 'contact_number' => '0917-100-5004', 'barangay' => 'Alburquerque', 'latitude' => 10.3100, 'longitude' => 123.9100, 'is_active' => true],
        ];

        foreach ($teams as $team) {
            ResponseTeam::firstOrCreate(
                ['code' => $team['code']],
                array_merge($team, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Seeded ' . count($teams) . ' response teams.');
    }
}
