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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->string('ip_address', 45)->nullable(false);
            $table->enum('environment', ['prod', 'staging', 'dev'])->default('prod');
            $table->enum('monitoring_type', ['online', 'offline'])->default('online');
            $table->float('cpu_usage')->nullable();
            $table->float('ram_usage')->nullable();
            $table->float('disk_usage')->nullable();
            $table->float('response_time')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
