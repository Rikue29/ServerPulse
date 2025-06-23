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
        Schema::create('notification_preferences', function (Blueprint $table) {
             $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->enum('alert_type', ['performance', 'log', 'heartbeat', 'system']);
        $table->enum('severity_min', ['low', 'medium', 'high', 'critical'])->default('medium');

        $table->boolean('via_email')->default(true);
        $table->boolean('via_slack')->default(false);
        $table->boolean('via_sms')->default(false);

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
