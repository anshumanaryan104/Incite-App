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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('frequency')->default(0);
            $table->integer('order')->default(1);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->enum('media_type', ['image','video','video_url'])->default('image');
            $table->string('media')->nullable();
            $table->string('video_url')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_link')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            // Adding indexes
            $table->index('title');
            $table->index('frequency');
            $table->index('order');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('media_type');
            $table->index('status');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
