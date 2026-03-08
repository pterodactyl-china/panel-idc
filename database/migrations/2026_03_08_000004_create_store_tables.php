<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 32)->default('points'); // points, server_days, custom
            $table->integer('value')->default(0); // e.g. number of points or server days
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 8)->default('CNY');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 64)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('subject')->default('');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 8)->default('CNY');
            $table->string('status', 32)->default('pending'); // pending, paid, cancelled, refunded
            $table->string('payment_method', 32)->nullable(); // alipay, alipay_face, wechat
            $table->string('payment_trade_no', 128)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
