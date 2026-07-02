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
        Schema::create('agenda_bidang', function (Blueprint $table) {
            $table->unsignedBigInteger('agenda_id');
            $table->unsignedBigInteger('bidang_id');

            $table->primary(['agenda_id', 'bidang_id']);

            $table->foreign('agenda_id')
                ->references('id')
                ->on('agenda')
                ->onDelete('cascade');

            $table->foreign('bidang_id')
                ->references('id')
                ->on('bidang')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_bidang');
    }
};
