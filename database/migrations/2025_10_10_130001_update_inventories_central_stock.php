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
        // The inventories table already has the correct structure from the previous migration
        // This migration is just a placeholder to track central inventory feature
        // No changes needed as branch_id is already nullable
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to reverse
    }
};