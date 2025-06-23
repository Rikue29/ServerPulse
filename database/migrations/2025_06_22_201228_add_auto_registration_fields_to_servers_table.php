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
            $table->string('registration_token')->nullable()->unique();
            $table->boolean('auto_register')->default(false);
            $table->timestamp('token_expires_at')->nullable();
            $table->json('allowed_ips')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['registration_token', 'auto_register', 'token_expires_at', 'allowed_ips']);
        });
    }
};
