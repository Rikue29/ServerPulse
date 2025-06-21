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
            $table->boolean('agent_enabled')->default(false)->after('monitoring_type');
            $table->string('agent_id')->nullable()->unique()->after('agent_enabled');
            $table->string('agent_token')->nullable()->after('agent_id');
            $table->timestamp('agent_last_heartbeat')->nullable()->after('agent_token');
            $table->enum('agent_status', ['inactive', 'active', 'disconnected'])->default('inactive')->after('agent_last_heartbeat');
            $table->string('agent_version')->nullable()->after('agent_status');
            $table->json('agent_config')->nullable()->after('agent_version');
            $table->json('last_metrics')->nullable()->after('agent_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'agent_enabled',
                'agent_id',
                'agent_token',
                'agent_last_heartbeat',
                'agent_status',
                'agent_version',
                'agent_config',
                'last_metrics'
            ]);
        });
    }
};
