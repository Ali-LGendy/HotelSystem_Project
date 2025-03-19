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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Foreign Keys
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('floor_id')->nullable()->constrained('floors')->onDelete('cascade');

            // Room Details
            $table->string('room_number')->unique();
            $table->unsignedInteger('room_capacity');
            $table->unsignedInteger('price');
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
