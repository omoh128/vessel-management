<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xml_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 300);
            $table->string('source', 80)->default('manual_upload'); // manual_upload | cron | webhook
            $table->integer('total_records')->default(0);
            $table->integer('inserted')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('skipped')->default(0);
            $table->integer('failed')->default(0);
            $table->json('errors')->nullable();         // Array of per-record errors
            $table->enum('status', ['pending', 'processing', 'complete', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_import_logs');
    }
};