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
        Schema::create('blog_pool_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blog_pool_question_id')->default(0);
            $table->string('option')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('blog_pool_question_id')->references('id')->on('blog_pool_questions')->onDelete('cascade');
            // Adding indexes
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_pool_options');
    }
};
