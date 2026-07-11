<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_reservations', function (Blueprint $table) {
            $table->dateTime('actual_end_datetime')->nullable()->after('end_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_reservations', function (Blueprint $table) {
            $table->dropColumn('actual_end_datetime');
        });
    }
};
