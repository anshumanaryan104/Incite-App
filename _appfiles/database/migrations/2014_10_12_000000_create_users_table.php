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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['admin','user','subadmin'])->default('user');
            $table->enum('device_type', ['android','ios'])->default('android');
            $table->enum('login_from', ['email','google','facebook','apple'])->default('email');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('photo')->nullable();
            $table->string('phone', 25)->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->string('fb_token')->nullable();
            $table->string('apple_token')->nullable();
            $table->string('google_token')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('api_token')->nullable();
            $table->integer('otp')->default(0);
            $table->integer('role_id')->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Adding indexes
            $table->index('type');
            $table->index('device_type');
            $table->index('login_from');
            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('api_token');
            $table->index('otp');
            $table->index('role_id');
            $table->index('email_verified_at');
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
        Schema::dropIfExists('users');
    }
};
