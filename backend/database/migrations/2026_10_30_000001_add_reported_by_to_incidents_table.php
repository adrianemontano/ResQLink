<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('incidents') || Schema::hasColumn('incidents', 'reported_by')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            $table->foreignId('reported_by')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('incidents') || ! Schema::hasColumn('incidents', 'reported_by')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropForeign(['reported_by']);
            $table->dropColumn('reported_by');
        });
    }
};
