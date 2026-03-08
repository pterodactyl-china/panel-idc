<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            // Daily points cost for this server, calculated from resource limits.
            // NULL means the server has not been assigned a point cost yet.
            $table->integer('points_per_day')->nullable()->default(null)->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('points_per_day');
        });
    }
};
