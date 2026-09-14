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

        if (! Schema::hasTable('volunteer_documents')) {
            Schema::create('volunteer_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('volunteer_id')->constrained('users')->cascadeOnDelete();
                $table->enum('document_type', ['endorsement', 'clearance', 'residency']);
                $table->string('file_path');
                $table->string('original_filename');
                $table->timestamp('uploaded_at')->useCurrent();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->index(['volunteer_id', 'document_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_documents');
        Schema::dropIfExists('volunteer_profiles');
    }
};
