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
            $table->decimal('cpu_usage', 5, 2)->nullable(); // Percentage with 2 decimal places
            $table->decimal('ram_usage', 5, 2)->nullable(); // Percentage with 2 decimal places
            $table->decimal('disk_usage', 5, 2)->nullable(); // Percentage with 2 decimal places
            $table->bigInteger('network_rx')->nullable(); // Network received bytes
            $table->bigInteger('network_tx')->nullable(); // Network transmitted bytes
            $table->bigInteger('disk_io_read')->nullable(); // Disk read bytes
            $table->bigInteger('disk_io_write')->nullable(); // Disk write bytes
            $table->decimal('response_time', 8, 3)->nullable(); // Response time in milliseconds
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['server_id', 'created_at']);
            $table->index('created_at');
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
