<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('barangays')) {
            Schema::create('barangays', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('incident_categories')) {
            Schema::create('incident_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 50)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        foreach ([['severity_levels', 'severity'], ['incident_statuses', 'status']] as [$tableName, $label]) {
            if (Schema::hasTable($tableName)) {
                continue;
            }

            Schema::create($tableName, function (Blueprint $table) use ($label): void {
                $table->id();
                $table->string('name', 20)->unique();
                $table->unsignedInteger('sort_order')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_statuses');
        Schema::dropIfExists('severity_levels');
        Schema::dropIfExists('incident_categories');
        Schema::dropIfExists('barangays');
    }
};
