<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // vat_rate, sscl_rate wage
            $table->string('value');
            $table->timestamps();
        });

        // Default rates tika ethulath karamu
        DB::table('settings')->insert([
            ['key' => 'vat_rate', 'value' => '18'],
            ['key' => 'sscl_rate', 'value' => '2.5'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
