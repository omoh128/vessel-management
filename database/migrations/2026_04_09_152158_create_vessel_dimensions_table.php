<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_dimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained('vessels')->cascadeOnDelete();
            $table->decimal('loa_m', 6, 2)->nullable();    // Length overall (metres)
            $table->decimal('beam_m', 6, 2)->nullable();   // Beam width (metres)
            $table->decimal('draft_m', 6, 2)->nullable();  // Draft depth (metres)
            $table->integer('weight_kg')->nullable();       // Displacement weight
            $table->decimal('mast_height_m', 6, 2)->nullable(); // Sailboats only
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_dimensions');
    }
};