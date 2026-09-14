<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', 'description')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->string('description')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name', 100)->nullable()->after('password');
                }
                if (! Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name', 100)->nullable()->after('first_name');
                }
                if (! Schema::hasColumn('users', 'contact_number')) {
                    $table->string('contact_number', 20)->nullable()->after('last_name');
                }
            });
        }

        if (! Schema::hasTable('incidents')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            if (! Schema::hasColumn('incidents', 'volunteer_id')) {
                $table->foreignId('volunteer_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('incidents', 'category_id')) {
                $table->foreignId('category_id')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'barangay_id')) {
                $table->foreignId('barangay_id')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'severity_id')) {
                $table->foreignId('severity_id')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'status_id')) {
                $table->foreignId('status_id')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'nearest_landmark')) {
                $table->string('nearest_landmark')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'impact_radius')) {
                $table->unsignedInteger('impact_radius')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'affected_population')) {
                $table->unsignedInteger('affected_population')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'reported_at')) {
                $table->timestamp('reported_at')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable();
            }
            if (! Schema::hasColumn('incidents', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Historical data is intentionally left untouched by this compatibility migration.
    }
};
