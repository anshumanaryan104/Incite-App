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
        if (!Schema::hasTable('blog_visibilities')) {
            Schema::create('blog_visibilities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('visibility_id')->default(0);
                $table->unsignedBigInteger('blog_id')->default(0);
                $table->timestamps();
                $table->foreign('visibility_id')->references('id')->on('visibilities')->onDelete('cascade');
                $table->foreign('blog_id')->references('id')->on('blogs')->onDelete('cascade');
                // Adding indexes
                $table->index('created_at');
                $table->index('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_visibilities');
    }
};
