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
    Schema::table('branches', function (Blueprint $table) {
        $table->string('vat_reg_no')->nullable()->after('telephone');
    });
}

public function down()
{
    Schema::table('branches', function (Blueprint $table) {
        $table->dropColumn('vat_reg_no');
    });
}
};
