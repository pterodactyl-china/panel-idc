<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add server-package resource fields to products.
        Schema::table('products', function (Blueprint $table) {
            // Server package configuration (only used when type = 'server')
            $table->unsignedInteger('location_id')->nullable()->after('sort_order');
            $table->unsignedBigInteger('node_id')->nullable()->after('location_id');
            $table->unsignedBigInteger('egg_id')->nullable()->after('node_id');
            $table->integer('cpu')->nullable()->comment('CPU limit % (100 = 1 core)')->after('egg_id');
            $table->integer('memory')->nullable()->comment('Memory limit MB')->after('cpu');
            $table->integer('disk')->nullable()->comment('Disk limit MB')->after('memory');
            $table->integer('databases')->nullable()->default(0)->after('disk');
            $table->integer('backups')->nullable()->default(0)->after('databases');
            $table->integer('allocations')->nullable()->default(1)->after('backups');
        });

        // Payment gateway settings
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default payment settings (all disabled)
        DB::table('payment_settings')->insert([
            ['key' => 'alipay_enabled',     'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alipay_app_id',      'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alipay_private_key', 'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alipay_public_key',  'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alipay_face_enabled','value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'alipay_face_code',   'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wechat_enabled',     'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wechat_app_id',      'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wechat_mch_id',      'value' => '',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'wechat_api_key',     'value' => '',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['location_id', 'node_id', 'egg_id', 'cpu', 'memory', 'disk', 'databases', 'backups', 'allocations']);
        });
    }
};
