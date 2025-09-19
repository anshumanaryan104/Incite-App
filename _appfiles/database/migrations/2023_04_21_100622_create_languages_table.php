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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code_id')->default(0);
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->enum('position', ['rtl','ltr'])->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_default')->default(0);
            $table->timestamps();
            $table->softDeletes();
            // Adding indexes
            $table->index('code_id');
            $table->index('name');
            $table->index('code');
            $table->index('position');
            $table->index('status');
            $table->index('is_default');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
