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
        Schema::create('video_generation_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('workflow_id')->unique();
            $table->string('url')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('current_step', ['diffbot', 'chatgpt', 'video_generation', 'completed'])->default('diffbot');
            $table->json('diffbot_data')->nullable();
            $table->json('processed_data')->nullable();
            $table->string('result')->nullable();
            $table->json('metadata')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_generation_jobs');
    }
};