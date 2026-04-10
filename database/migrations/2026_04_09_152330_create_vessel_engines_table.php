<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained('vessels')->cascadeOnDelete();
            $table->string('make', 100)->nullable();      // e.g. Volvo Penta
            $table->string('model', 100)->nullable();     // e.g. D2-40
            $table->smallInteger('power_hp')->nullable(); // Horsepower
            $table->smallInteger('hours')->nullable();    // Engine hours
            $table->enum('fuel_type', ['diesel', 'petrol', 'electric', 'hybrid'])->default('diesel');
            $table->year('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_engines');
    }
};