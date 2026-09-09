<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('volunteer_profiles')) {
            Schema::create('volunteer_profiles', function (Blueprint $table): void {
                $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
                $table->string('barangay', 100);
                $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};
