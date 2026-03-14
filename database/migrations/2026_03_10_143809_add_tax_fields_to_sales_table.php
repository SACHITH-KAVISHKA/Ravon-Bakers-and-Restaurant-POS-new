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
        Schema::table('sales', function (Blueprint $table) {
            // subtotal එකට පස්සේ මේ column දෙක decimal විදියට එකතු කරනවා
            $table->decimal('sscl_amount', 15, 2)->after('subtotal')->default(0);
            $table->decimal('vat_amount', 15, 2)->after('sscl_amount')->default(0);
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['sscl_amount', 'vat_amount']);
        });
    }
};
