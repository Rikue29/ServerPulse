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
        Schema::table('logs', function (Blueprint $table) {
            $table->index(['level', 'created_at']);
            $table->index(['server_id', 'created_at']);
            $table->index(['created_at']);
            $table->index(['level']);
            $table->index(['server_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['level', 'created_at']);
            $table->dropIndex(['server_id', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['level']);
            $table->dropIndex(['server_id']);
        });
    }
};
