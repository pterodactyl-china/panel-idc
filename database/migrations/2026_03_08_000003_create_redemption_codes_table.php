<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemption_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('type', 32)->default('points'); // points, days, product
            $table->integer('value')->default(0); // points amount, or days
            $table->unsignedInteger('uses_total')->default(1);
            $table->unsignedInteger('uses_remaining')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('redemption_code_uses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('redemption_code_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('redemption_code_id')->references('id')->on('redemption_codes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['redemption_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemption_code_uses');
        Schema::dropIfExists('redemption_codes');
    }
};
