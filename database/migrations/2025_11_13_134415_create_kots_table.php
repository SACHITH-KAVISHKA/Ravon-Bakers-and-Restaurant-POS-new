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
        Schema::create('kots', function (Blueprint $table) {
            $table->id();
            $table->string('kot_no')->unique();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->enum('type', ['KOT', 'BOT'])->default('KOT'); // KOT for kitchen, BOT for bar
            $table->json('items'); // Store items as JSON
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'preparing', 'ready', 'served'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kots');
    }
};
