<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->timestamp('last_down_at')->nullable();
            $table->timestamp('running_since')->nullable();
            $table->bigInteger('total_uptime_seconds')->default(0);
            $table->bigInteger('total_downtime_seconds')->default(0);
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['last_down_at', 'running_since', 'total_uptime_seconds', 'total_downtime_seconds']);
        });
    }
};
