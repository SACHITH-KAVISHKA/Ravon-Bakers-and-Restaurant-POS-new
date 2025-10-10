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
        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            
            // Add unique constraint for item_id + branch_id combination
            $table->unique(['item_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropUnique(['item_id', 'branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};