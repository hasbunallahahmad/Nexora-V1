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
        Schema::table('room_reservations', function (Blueprint $table): void {
            $table->foreignId('requested_by')->nullable()->change();
            $table->string('guest_name', 150)->nullable()->after('requested_by');
            $table->string('guest_contact', 150)->nullable()->after('guest_name');
            $table->string('guest_instansi', 150)->nullable()->after('guest_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_reservations', function (Blueprint $table): void {
            $table->dropColumn(['guest_name', 'guest_contact', 'guest_instansi']);
            $table->foreignId('requested_by')->nullable(false)->change();
        });
    }
};
