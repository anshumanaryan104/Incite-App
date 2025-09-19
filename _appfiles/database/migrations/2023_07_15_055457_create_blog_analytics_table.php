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
        if (!Schema::hasTable('blog_analytics')) {
            Schema::create('blog_analytics', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['view','share','poll_share','blog_time_spent','app_time_spent'])->default('view');
                $table->unsignedBigInteger('user_id')->default(0);
                $table->unsignedBigInteger('blog_id')->default(0);
                $table->dateTime('start_date_time')->nullable();
                $table->dateTime('end_date_time')->nullable();
                $table->timestamps();
                $table->softDeletes();
                 // Adding indexes
                $table->index('type');
                $table->index('user_id');
                $table->index('blog_id');
                $table->index('start_date_time');
                $table->index('end_date_time');
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
        Schema::dropIfExists('blog_analytics');
    }
};
