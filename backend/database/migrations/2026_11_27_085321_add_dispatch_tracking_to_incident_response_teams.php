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
        Schema::table('incident_response_teams', function (Blueprint $table) {
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('cancelled_at');
            $table->text('cancel_reason')->nullable()->after('cancelled_by');
            $table->index(['incident_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_response_teams', function (Blueprint $table) {
            $table->dropIndex(['incident_id', 'status']);
            $table->dropForeign(['dispatched_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['dispatched_by', 'cancelled_by', 'cancel_reason']);
        });
    }
};
