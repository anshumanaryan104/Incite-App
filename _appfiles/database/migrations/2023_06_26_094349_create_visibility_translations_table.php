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
        if (!Schema::hasTable('visibility_translations')) {
            Schema::create('visibility_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('visibility_id')->default(0);
                $table->string('language_code')->nullable();
                $table->text('display_name')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('visibility_id')->references('id')->on('visibilities')->onDelete('cascade');
                // Adding indexes
                $table->index('language_code');
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
        Schema::dropIfExists('visibility_translations');
    }
};
