<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table): void {
            if (! Schema::hasColumn('incidents', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('reported_at');
            }
            if (! Schema::hasColumn('incidents', 'dispatched_team_id')) {
                $table->foreignId('dispatched_team_id')
                    ->nullable()
                    ->after('status_id')
                    ->constrained('response_teams')
                    ->nullOnDelete();
            }
        });

        Schema::table('incident_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('incident_histories', 'response_team_id')) {
                $table->foreignId('response_team_id')
                    ->nullable()
                    ->after('status_id')
                    ->constrained('response_teams')
                    ->nullOnDelete();
            }
        });

        Schema::table('incident_photos', function (Blueprint $table): void {
            if (! Schema::hasColumn('incident_photos', 'media_type')) {
                $table->string('media_type', 20)->default('image')->after('mime_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incident_photos', function (Blueprint $table): void {
            if (Schema::hasColumn('incident_photos', 'media_type')) {
                $table->dropColumn('media_type');
            }
        });

        Schema::table('incident_histories', function (Blueprint $table): void {
            if (Schema::hasColumn('incident_histories', 'response_team_id')) {
                $table->dropConstrainedForeignId('response_team_id');
            }
        });

        Schema::table('incidents', function (Blueprint $table): void {
            if (Schema::hasColumn('incidents', 'dispatched_team_id')) {
                $table->dropConstrainedForeignId('dispatched_team_id');
            }
            if (Schema::hasColumn('incidents', 'received_at')) {
                $table->dropColumn('received_at');
            }
        });
    }
};
