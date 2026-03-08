<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Per-product daily-points deduction rates for server-type products.
            // When set, these override the global PointsCalculator rates.
            $table->integer('points_cpu_rate')->nullable()
                ->comment('Points per 100% CPU per day (overrides global rate)')->after('allocations');
            $table->integer('points_memory_rate')->nullable()
                ->comment('Points per GB memory per day (overrides global rate)')->after('points_cpu_rate');
            $table->integer('points_disk_rate')->nullable()
                ->comment('Points per GB disk per day (overrides global rate)')->after('points_memory_rate');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['points_cpu_rate', 'points_memory_rate', 'points_disk_rate']);
        });
    }
};
