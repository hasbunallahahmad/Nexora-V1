<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add additional database constraints to improve data integrity and validation
     */
    public function up(): void
    {
        // Improve agenda table constraints
        Schema::table('agenda', function (Blueprint $table) {
            // Make location NOT NULL (currently nullable)
            $table->string('location')->nullable(false)->change();

            // Add index for published status queries
            $table->index('is_published');

            // Add composite index for common queries
            $table->index(['is_published', 'start_date']);
        });

        // Add CHECK constraint for end_date >= start_date using raw SQL
        // This ensures end_date is always after or equal to start_date
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE agenda ADD CONSTRAINT check_end_date_after_start CHECK (end_date IS NULL OR end_date >= start_date)');
        }

        // Improve bidang table constraints
        Schema::table('bidang', function (Blueprint $table) {
            // Add unique constraint on nama_bidang
            $table->unique('nama_bidang');

            // Add index for search queries
            $table->index('nama_bidang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda', function (Blueprint $table) {
            $table->dropIndex(['is_published']);
            $table->dropIndex(['is_published', 'start_date']);
            $table->string('location')->nullable()->change();
        });

        // Drop CHECK constraint using raw SQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE agenda DROP CONSTRAINT check_end_date_after_start');
        }

        Schema::table('bidang', function (Blueprint $table) {
            $table->dropUnique(['nama_bidang']);
            $table->dropIndex(['nama_bidang']);
        });
    }
};
