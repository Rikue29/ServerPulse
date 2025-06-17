<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('logs', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('logs', 'server_id')) {
                $table->foreignId('server_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('logs', 'level')) {
                $table->string('level')->after('server_id');
            }
            if (!Schema::hasColumn('logs', 'context')) {
                $table->json('context')->nullable()->after('message');
            }
        });
    }

    public function down()
    {
        Schema::table('logs', function (Blueprint $table) {
            // Remove new columns if they exist
            if (Schema::hasColumn('logs', 'server_id')) {
                $table->dropForeign(['server_id']);
                $table->dropColumn('server_id');
            }
            if (Schema::hasColumn('logs', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('logs', 'context')) {
                $table->dropColumn('context');
            }
        });
    }
}; 