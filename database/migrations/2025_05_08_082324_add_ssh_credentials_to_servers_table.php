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
            $table->string('ssh_user')->nullable();
            $table->text('ssh_password')->nullable();
            $table->text('ssh_key')->nullable();
            $table->integer('ssh_port')->default(22);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['ssh_user', 'ssh_password', 'ssh_key', 'ssh_port']);
        });
    }
};
