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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['post','quote'])->default('post');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_link')->nullable();
            $table->string('voice')->nullable();
            $table->integer('post_id')->default(0);
            $table->string('accent_code')->nullable();
            $table->string('tags')->nullable();
            $table->text('short_description')->nullable();
            $table->string('video_url')->nullable();
            $table->string('audio_file')->nullable();
            $table->enum('content_type', ['text','video','audio'])->nullable();
            $table->string('social_media_image')->nullable();
            $table->double('reading_time')->default(0);   
            $table->string('seo_title')->nullable();
            $table->string('seo_keyword')->nullable();
            $table->string('seo_tag')->nullable();
            $table->string('seo_description')->nullable();
            $table->tinyInteger('is_voting_enable')->default(0);
            $table->unsignedBigInteger('created_by')->default(0);
            $table->string('author_name')->nullable();
            $table->string('background_image')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('schedule_date')->nullable();
            $table->integer('order')->default(0);
            $table->tinyInteger('is_featured')->default(0);
            $table->timestamps();
            $table->softDeletes();
            // Adding indexes
            $table->index('type');
            $table->index('title');
            $table->index('slug');
            $table->index('is_voting_enable');
            $table->index('created_by');
            $table->index('schedule_date');
            $table->index('order');
            $table->index('is_featured');
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
        Schema::dropIfExists('blogs');
    }
};
