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
        Schema::table('kots', function (Blueprint $table) {
            // Remove status-related columns
            $table->dropColumn(['status', 'prepared_at', 'served_at', 'completed_at']);
            // Remove order-related columns not needed for auto-created KOTs
            $table->dropColumn(['table_no', 'order_type']);
        });

        Schema::table('kot_items', function (Blueprint $table) {
            // Remove status column from items
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kots', function (Blueprint $table) {
            // Restore columns
            $table->string('table_no')->nullable();
            $table->enum('order_type', ['Dine-In', 'Take-Away', 'Delivery'])->default('Dine-In');
            $table->enum('status', ['Pending', 'Preparing', 'Ready', 'Served', 'Completed', 'Cancelled'])->default('Pending');
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::table('kot_items', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Preparing', 'Ready'])->default('Pending');
        });
    }
};
