<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->enum('alert_type', [
                'performance', 'system', 'cpu', 'ram', 'memory', 'disk', 'network', 'load', 'log', 'heartbeat'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->enum('alert_type', [
                'performance', 'log', 'heartbeat', 'system'
            ])->change();
        });
    }
};
