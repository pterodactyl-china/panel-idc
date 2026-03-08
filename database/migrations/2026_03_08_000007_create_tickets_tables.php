<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('title', 191);
            $table->text('content');
            $table->string('status', 32)->default('open'); // open, in_progress, closed
            $table->string('priority', 16)->default('normal'); // low, normal, high, urgent
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedInteger('user_id');
            $table->text('content');
            $table->boolean('is_staff')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
    }
};
