<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify the existing role enum to include supervisor
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'supervisor') DEFAULT 'staff'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to original enum values
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff') DEFAULT 'staff'");
        });
    }
};
