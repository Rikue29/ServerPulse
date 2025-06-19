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
            // Alternative network metrics
            $table->bigInteger('network_throughput')->default(0)->after('network_speed'); // Bytes per interval
            $table->bigInteger('total_network_rx')->default(0)->after('network_throughput'); // Total RX since start
            $table->bigInteger('total_network_tx')->default(0)->after('total_network_rx'); // Total TX since start
            $table->integer('network_connections')->default(0)->after('total_network_tx'); // Active connections
            $table->float('network_utilization')->default(0)->after('network_connections'); // Percentage utilization
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['network_throughput', 'total_network_rx', 'total_network_tx', 'network_connections', 'network_utilization']);
        });
    }
};
