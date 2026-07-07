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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('plate_number', 20)->unique();
            $table->string('type', 50)->nullable();
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_contact', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
