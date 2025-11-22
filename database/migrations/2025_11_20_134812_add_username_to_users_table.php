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
        // Add username column as nullable first (if it doesn't exist)
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }

        // Populate usernames from email addresses for existing users with null usernames
        if (Schema::hasColumn('users', 'email')) {
            $users = DB::table('users')->whereNull('username')->orWhere('username', '')->get();
            foreach ($users as $user) {
                $username = explode('@', $user->email)[0]; // Use email prefix as username
                $baseUsername = $username;
                $counter = 1;
                
                // Ensure unique username
                while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
                
                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            }
        } else {
            // If email column doesn't exist, generate usernames for users without one
            $users = DB::table('users')->whereNull('username')->orWhere('username', '')->get();
            foreach ($users as $user) {
                $username = 'user' . $user->id;
                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            }
        }

        // Now make username unique and remove email
        // Try to add unique constraint (will fail silently if already exists)
        try {
            DB::statement('ALTER TABLE users MODIFY COLUMN username VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE users ADD UNIQUE KEY users_username_unique (username)');
        } catch (\Exception $e) {
            // Unique constraint might already exist, that's okay
        }
        
        // Drop email columns if they exist
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'email')) {
                try {
                    $table->dropUnique(['email']);
                } catch (\Exception $e) {
                    // Index might not exist
                }
                $table->dropColumn('email');
            }
            
            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
