<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->string('bot_id')->unique(); // Contoh: BOT_001
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'connecting', 'error'])->default('inactive');
            $table->text('session_path')->nullable();
            $table->json('active_plugins')->nullable(); // Plugin yang aktif per bot
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};