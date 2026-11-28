<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (! Schema::hasColumn('incidents', 'external_team_name')) {
                $table->string('external_team_name')->nullable()->after('dispatched_team_id');
            }
        });

        Schema::table('incident_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('incident_histories', 'external_team_name')) {
                $table->string('external_team_name')->nullable()->after('response_team_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'external_team_name')) {
                $table->dropColumn('external_team_name');
            }
        });

        Schema::table('incident_histories', function (Blueprint $table) {
            if (Schema::hasColumn('incident_histories', 'external_team_name')) {
                $table->dropColumn('external_team_name');
            }
        });
    }
};
