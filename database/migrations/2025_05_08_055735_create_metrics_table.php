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
            $table->float('cpu_usage')->nullable();
            $table->float('ram_usage')->nullable();
            $table->float('disk_usage')->nullable();
            $table->bigInteger('network_rx')->nullable();
            $table->bigInteger('network_tx')->nullable();
            $table->bigInteger('disk_io_read')->nullable();
            $table->bigInteger('disk_io_write')->nullable();
            $table->float('response_time')->nullable();
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
