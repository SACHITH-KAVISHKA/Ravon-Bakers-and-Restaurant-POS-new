<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If a branch with id=1 does not exist, insert it as 'Main Branch'
        if (!DB::table('branches')->where('id', 1)->exists()) {
            DB::table('branches')->insert([
                'id' => 1,
                'name' => 'Main Branch',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // If id=1 exists but name is not 'Main Branch', update it
            DB::table('branches')->where('id', 1)->update(['name' => 'Main Branch']);
        }
    }

    public function down(): void
    {
        // Do not delete id=1 branch on rollback (to avoid accidental data loss)
    }
};
