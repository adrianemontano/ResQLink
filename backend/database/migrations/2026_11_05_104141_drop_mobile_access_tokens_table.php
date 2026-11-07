<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mobile_access_tokens');
    }

    public function down(): void
    {
        // The retired custom token table is intentionally not recreated.
    }
};
