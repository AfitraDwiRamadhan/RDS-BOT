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
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('bot_id')->nullable()->after('id')->constrained('bots')->onDelete('cascade');
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->foreignId('bot_id')->nullable()->after('id')->constrained('bots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['bot_id']);
            $table->dropColumn('bot_id');
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->dropForeign(['bot_id']);
            $table->dropColumn('bot_id');
        });
    }
};
