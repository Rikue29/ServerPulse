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
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedBigInteger('network_rx')->default(0)->after('disk_usage');
            $table->unsignedBigInteger('network_tx')->default(0)->after('network_rx');
            $table->float('network_speed')->default(0)->after('network_tx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['network_rx', 'network_tx', 'network_speed']);
        });
    }
};
