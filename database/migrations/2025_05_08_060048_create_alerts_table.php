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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('threshold_id')->constrained('alert_thresholds')->onDelete('cascade');
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->float('metric_value');
            $table->enum('status', ['triggered', 'resolved']);
            $table->enum('alert_type', ['performance', 'log', 'heartbeat', 'system']);
            $table->text('alert_message');
            $table->timestamp('alert_time');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
