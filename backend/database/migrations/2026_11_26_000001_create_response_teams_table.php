<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_teams', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 32)->nullable()->unique();
            $table->string('type', 80)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('response_teams')->insert([
            ['name' => 'Alpha Rescue Team', 'code' => 'TEAM-ALPHA', 'type' => 'Search & Rescue', 'contact_number' => '0917-100-0001', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bravo Medical / EMS', 'code' => 'TEAM-BRAVO', 'type' => 'Medical / EMS', 'contact_number' => '0917-100-0002', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Charlie Fire Response', 'code' => 'TEAM-CHARLIE', 'type' => 'Fire Suppression', 'contact_number' => '0917-100-0003', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Delta Flood & Swiftwater', 'code' => 'TEAM-DELTA', 'type' => 'Flood Response', 'contact_number' => '0917-100-0004', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Echo Rapid Assessment', 'code' => 'TEAM-ECHO', 'type' => 'Rapid Assessment', 'contact_number' => '0917-100-0005', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('response_teams');
    }
};
