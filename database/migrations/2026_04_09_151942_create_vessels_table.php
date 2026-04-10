<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 50)->unique()->nullable(); // from XML feed
            $table->enum('source', ['xml', 'manual'])->default('manual');
            $table->string('name', 200);
            $table->string('category', 80);          // Sailboat, Motorboat, Catamaran...
            $table->string('make', 100)->nullable();  // Manufacturer / builder
            $table->string('model', 100)->nullable();
            $table->smallInteger('year_built')->nullable();
            $table->enum('status', ['available', 'under_offer', 'sold'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('status');
            $table->index('year_built');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};