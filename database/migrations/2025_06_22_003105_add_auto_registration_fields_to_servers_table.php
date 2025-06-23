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
            $table->string('auth_token', 128)->nullable()->after('agent_token');
            $table->boolean('auto_registered')->default(false)->after('auth_token');
            $table->timestamp('last_seen')->nullable()->after('auto_registered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['auth_token', 'auto_registered', 'last_seen']);
        });
    }
};
