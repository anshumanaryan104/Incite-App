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
        Schema::create('live_news_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_news_id')->default(0);
            $table->string('language_code')->nullable();
            $table->string('company_name')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('live_news_id')->references('id')->on('live_news')->onDelete('cascade');
            // Adding indexes
            $table->index('language_code');
            $table->index('company_name');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_news_translations');
    }
};
