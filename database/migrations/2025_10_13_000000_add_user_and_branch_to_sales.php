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
        // Add nullable user_id and branch_id to sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('terminal');
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');
        });

        // Modify payment_method column to be VARCHAR(50) to avoid enum truncation issues
        // Use raw statement to avoid requiring doctrine/dbal in environments where not present
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `sales` MODIFY `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash'");
        } elseif ($driver === 'sqlite') {
            // SQLite: recreate table approach would be safer; for now leave as-is (enum is stored as text)
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE sales ALTER COLUMN payment_method TYPE varchar(50)");
            DB::statement("ALTER TABLE sales ALTER COLUMN payment_method SET DEFAULT 'cash'");
        }

        // Backfill user_id and branch_id for existing sales where a unique user match by name exists
        $sales = DB::table('sales')->select('id', 'user_name')->get();
        foreach ($sales as $sale) {
            if (empty($sale->user_name)) continue;

            // Try to find a single user with this name
            $users = DB::table('users')->where('name', $sale->user_name)->get();
            if ($users->count() === 1) {
                $user = $users->first();
                DB::table('sales')->where('id', $sale->id)->update([
                    'user_id' => $user->id,
                    'branch_id' => $user->branch_id,
                ]);
            }
            // If multiple users or none found: skip (manual reconciliation required)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('sales', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
        // Note: We do not revert payment_method column type automatically.
    }
};
