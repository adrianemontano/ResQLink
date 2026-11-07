<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_histories', function (Blueprint $table): void {
            $table->string('entry_type', 20)->default('status_change')->after('incident_id');
            $table->string('category', 50)->nullable()->after('entry_type');
            $table->index(['incident_id', 'entry_type']);
        });
    }

    public function down(): void
    {
        Schema::table('incident_histories', function (Blueprint $table): void {
            $table->dropIndex(['incident_id', 'entry_type']);
            $table->dropColumn(['entry_type', 'category']);
        });
    }
};
