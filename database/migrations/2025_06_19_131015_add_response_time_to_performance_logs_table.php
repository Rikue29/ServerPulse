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
        Schema::table('performance_logs', function (Blueprint $table) {
            $table->float('response_time')->nullable()->after('disk_io_write');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_logs', function (Blueprint $table) {
            $table->dropColumn('response_time');
        });
    }
};
