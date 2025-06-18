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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->float('cpu_usage');
            $table->float('ram_usage');
            $table->float('disk_usage');
            $table->unsignedBigInteger('network_rx');
            $table->unsignedBigInteger('network_tx');
            $table->unsignedBigInteger('disk_io_read');
            $table->unsignedBigInteger('disk_io_write');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
