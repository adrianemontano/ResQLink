<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incidents') && ! Schema::hasColumn('incidents', 'severity')) {
            Schema::table('incidents', function (Blueprint $table): void {
                $table->string('severity', 20)->nullable()->after('severity_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('incidents') && Schema::hasColumn('incidents', 'severity')) {
            Schema::table('incidents', function (Blueprint $table): void {
                $table->dropColumn('severity');
            });
        }
    }
};
