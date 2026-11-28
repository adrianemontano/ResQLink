<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('response_teams', function (Blueprint $table) {
            $table->string('barangay')->nullable()->after('type');
            $table->decimal('latitude', 10, 7)->nullable()->after('barangay');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->index(['barangay', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('response_teams', function (Blueprint $table) {
            $table->dropIndex(['barangay', 'type']);
            $table->dropColumn(['barangay', 'latitude', 'longitude']);
        });
    }
};
