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
            if (!Schema::hasColumn('servers', 'agent_enabled')) {
                $table->boolean('agent_enabled')->default(0);
            }
            if (!Schema::hasColumn('servers', 'agent_id')) {
                $table->string('agent_id')->nullable();
            }
            if (!Schema::hasColumn('servers', 'agent_token')) {
                $table->string('agent_token')->nullable();
            }
            if (!Schema::hasColumn('servers', 'agent_status')) {
                $table->string('agent_status')->nullable();
            }
            if (!Schema::hasColumn('servers', 'agent_version')) {
                $table->string('agent_version')->nullable();
            }
            if (!Schema::hasColumn('servers', 'agent_config')) {
                $table->json('agent_config')->nullable();
            }
            if (!Schema::hasColumn('servers', 'agent_last_heartbeat')) {
                $table->timestamp('agent_last_heartbeat')->nullable();
            }
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
                'agent_status',
                'agent_version',
                'agent_config',
                'agent_last_heartbeat'
            ]);
        });
    }
};
