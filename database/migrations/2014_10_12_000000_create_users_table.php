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
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('grade')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->string('password');
            $table->json('favorite_subjects')->nullable();
            $table->enum('role', ['student', 'admin'])->default('student');
            $table->enum('subscription_type', ['free', 'pro'])->default('free');
            $table->rememberToken();
            $table->timestamps();
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
