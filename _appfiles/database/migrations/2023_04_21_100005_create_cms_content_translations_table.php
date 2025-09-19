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
        Schema::create('cms_content_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cms_id')->default(0);
            $table->string('language_code')->nullable();
            $table->string('title')->nullable();
            $table->string('page_title')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_char')->nullable();
            $table->string('meta_desc')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cms_id')->references('id')->on('cms_contents')->onDelete('cascade');
            // Adding indexes
            $table->index('language_code');
            $table->index('title');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_content_translations');
    }
};
