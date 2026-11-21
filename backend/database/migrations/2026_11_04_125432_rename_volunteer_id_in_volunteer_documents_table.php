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
        Schema::table('volunteer_documents', function (Blueprint $table): void {
            if (Schema::hasColumn('volunteer_documents', 'volunteer_id')) {
                $table->renameColumn('volunteer_id', 'volunteer_profile_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('volunteer_documents', function (Blueprint $table): void {
            if (Schema::hasColumn('volunteer_documents', 'volunteer_profile_id')) {
                $table->renameColumn('volunteer_profile_id', 'volunteer_id');
            }
        });
    }
};
