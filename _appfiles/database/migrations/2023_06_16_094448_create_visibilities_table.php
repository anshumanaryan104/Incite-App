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
        Schema::create('visibilities', function (Blueprint $table) {
            $table->id();
            $table->string('display_name')->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_website')->default(0);
            $table->tinyInteger('is_app')->default(0);
            $table->timestamps();
            $table->softDeletes();
            // Adding indexes
            $table->index('display_name');
            $table->index('name');
            $table->index('is_website');
            $table->index('is_app');
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
        Schema::dropIfExists('visibilities');
    }
};
