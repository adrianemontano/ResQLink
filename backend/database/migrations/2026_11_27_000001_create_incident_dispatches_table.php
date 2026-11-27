<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->restrictOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispatched_at');
            $table->string('notes', 300)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 300)->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'cancelled_at'], 'incident_dispatches_incident_active_index');
            $table->index(['team_id', 'dispatched_at'], 'incident_dispatches_team_report_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_dispatches');
    }
};
