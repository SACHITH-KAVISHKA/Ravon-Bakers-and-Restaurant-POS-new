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
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Check if the column exists before trying to drop it
            if (Schema::hasColumn('stock_transfers', 'from_branch_id')) {
                // Drop foreign key constraint first
                $table->dropForeign(['from_branch_id']);
                // Then drop the column
                $table->dropColumn('from_branch_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            // Re-add the column and foreign key
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->onDelete('cascade');
        });
    }
};
