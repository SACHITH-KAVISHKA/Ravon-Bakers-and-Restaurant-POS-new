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
            // Check if columns exist before dropping them
            if (Schema::hasColumn('items', 'stock_quantity')) {
                $table->dropColumn('stock_quantity');
            }
            if (Schema::hasColumn('items', 'image')) {
                $table->dropColumn('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0);
            $table->string('image')->nullable();
        });
    }
};
