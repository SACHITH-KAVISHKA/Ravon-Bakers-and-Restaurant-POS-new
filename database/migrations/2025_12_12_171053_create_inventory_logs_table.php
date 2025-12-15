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
        Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                // inventories table එකේ id එක (foreign key)
                $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');


                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('branch_id')->nullable();

                $table->integer('previous_stock');
                $table->integer('new_stock');
                $table->integer('quantity_change');

                $table->string('reason')->nullable(); //Purchase, Sale, Adjustment
                $table->unsignedBigInteger('user_id')->nullable();

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
