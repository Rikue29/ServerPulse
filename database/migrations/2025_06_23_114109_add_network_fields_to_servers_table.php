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
            // Add network fields if they don't exist
            if (!Schema::hasColumn('servers', 'network_rx')) {
                $table->bigInteger('network_rx')->default(0)->comment('Network received bytes counter');
            }
            if (!Schema::hasColumn('servers', 'network_tx')) {
                $table->bigInteger('network_tx')->default(0)->comment('Network transmitted bytes counter');
            }
            if (!Schema::hasColumn('servers', 'disk_io_read')) {
                $table->bigInteger('disk_io_read')->default(0)->comment('Disk read bytes counter');
            }
            if (!Schema::hasColumn('servers', 'disk_io_write')) {
                $table->bigInteger('disk_io_write')->default(0)->comment('Disk write bytes counter');
            }
            if (!Schema::hasColumn('servers', 'network_speed')) {
                $table->integer('network_speed')->default(0)->comment('Network link speed in Mbps');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            // Remove the columns added in the up method
            $table->dropColumn([
                'network_rx',
                'network_tx',
                'disk_io_read',
                'disk_io_write',
                'network_speed'
            ]);
        });
    }
};
