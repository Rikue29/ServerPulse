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
            // Drop existing columns
            $table->dropForeign(['server_id']);
            $table->dropColumn(['server_id', 'source', 'log_level']);
            
            // Add new columns
            $table->timestamp('timestamp')->after('id');
            $table->string('type')->after('timestamp');
            $table->string('category')->after('type');
            $table->string('server')->after('category');
            $table->string('status')->after('server');
            // message column already exists
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['timestamp', 'type', 'category', 'server', 'status']);
            
            // Restore original columns
            $table->foreignId('server_id')->after('id')->constrained()->onDelete('cascade');
            $table->string('source', 50)->after('server_id');
            $table->enum('log_level', ['INFO', 'WARN', 'ERROR'])->after('source');
        });
    }
}; 