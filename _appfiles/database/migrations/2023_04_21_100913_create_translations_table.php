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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id')->default(0);
            $table->string('group', 191)->nullable();
            $table->text('keyword')->nullable();
            $table->text('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            // Adding indexes
            $table->index('group');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
