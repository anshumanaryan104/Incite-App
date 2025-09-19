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
        Schema::create('e_paper_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('e_paper_id')->default(0);
            $table->string('language_code')->nullable();
            $table->string('name')->nullable();
            $table->string('pdf')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('e_paper_id')->references('id')->on('e_papers')->onDelete('cascade');
            // Adding indexes
            $table->index('language_code');
            $table->index('name');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_paper_translations');
    }
};
