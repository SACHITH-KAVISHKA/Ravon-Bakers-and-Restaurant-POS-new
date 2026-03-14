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
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('vat_applicable')->default(true)->after('stock_count'); // Adding VAT applicability
            $table->boolean('sscl_applicable')->default(true)->after('vat_applicable'); // Adding SSCL applicability
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['vat_applicable', 'sscl_applicable']);
        });
    }
};
