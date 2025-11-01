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
            $table->string('kot_no')->unique(); // KOT/BOT number
            $table->enum('type', ['KOT', 'BOT']); // Kitchen Order Ticket or Bar Order Ticket
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('cascade'); // Link to sale if completed
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cashier/Waiter who created
            $table->string('user_name');
            $table->string('table_no')->nullable(); // Table number for dine-in
            $table->enum('order_type', ['Dine-In', 'Take-Away', 'Delivery'])->default('Dine-In');
            $table->enum('status', ['Pending', 'Preparing', 'Ready', 'Served', 'Completed', 'Cancelled'])->default('Pending');
            $table->text('notes')->nullable(); // Special instructions
            $table->timestamp('prepared_at')->nullable(); // When kitchen/bar marked as prepared
            $table->timestamp('served_at')->nullable(); // When marked as served
            $table->timestamp('completed_at')->nullable(); // When converted to sale
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
