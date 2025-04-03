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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained('airlines');
            $table->string('origin', 3);
            $table->string('destination', 3);
            $table->integer('duration'); // Duration in minutes
            $table->dateTime('flight_date');
            $table->boolean('state')->default(false); // 0: Scheduled, 1: Completed
            $table->integer('passenger_capacity'); // Added passenger capacity field
            $table->timestamps();
            
            $table->foreign('origin')->references('id')->on('airports');
            $table->foreign('destination')->references('id')->on('airports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
