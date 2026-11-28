<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('response_teams', function (Blueprint $table) {
            if (! Schema::hasColumn('response_teams', 'team_leader')) {
                $table->string('team_leader')->nullable()->after('type');
            }
            if (! Schema::hasColumn('response_teams', 'members')) {
                $table->text('members')->nullable()->after('team_leader');
            }
        });
    }

    public function down(): void
    {
        Schema::table('response_teams', function (Blueprint $table) {
            if (Schema::hasColumn('response_teams', 'team_leader')) {
                $table->dropColumn('team_leader');
            }
            if (Schema::hasColumn('response_teams', 'members')) {
                $table->dropColumn('members');
            }
        });
    }
};
