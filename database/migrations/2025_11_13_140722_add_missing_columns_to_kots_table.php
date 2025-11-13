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
            // Check and add columns if they don't exist
            if (!Schema::hasColumn('kots', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kots', 'user_name')) {
                $table->string('user_name')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kots', function (Blueprint $table) {
            if (Schema::hasColumn('kots', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('kots', 'user_name')) {
                $table->dropColumn('user_name');
            }
        });
    }
};
