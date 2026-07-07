<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'done', 'cancelled', 'revised']);
            $table->string('note')->nullable(); // Catatan status
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin yang mengubah (jika via web)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};