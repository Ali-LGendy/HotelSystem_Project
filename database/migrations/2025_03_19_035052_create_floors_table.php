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
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Floor Details
            $table->string('name')->unique(); // i think it should be unique
            $table->unsignedBigInteger('number')->unique(); // i think it should be unique

            // Manager Relationship
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            // cascade if deleting manager will delete all his floors, and set null if deleting manager wont delete his floors
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
