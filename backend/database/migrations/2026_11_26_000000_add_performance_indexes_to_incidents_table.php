<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Index for status filtering (dashboard status chips, counts, scoping)
            $table->index('status', 'idx_incidents_status');

            // Index for date-based sorting and trend queries
            $table->index('reported_at', 'idx_incidents_reported_at');

            // Index for volunteer-scoped queries (volunteer dashboard + incident list)
            $table->index('volunteer_id', 'idx_incidents_volunteer_id');

            // Composite index for category/barangay filtering
            $table->index(['category', 'barangay'], 'idx_incidents_category_barangay');

            // Composite index for status + reported_at (most common query pattern)
            $table->index(['status', 'reported_at'], 'idx_incidents_status_reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_status');
            $table->dropIndex('idx_incidents_reported_at');
            $table->dropIndex('idx_incidents_volunteer_id');
            $table->dropIndex('idx_incidents_category_barangay');
            $table->dropIndex('idx_incidents_status_reported_at');
        });
    }
};
