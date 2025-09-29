<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'dibayarkan')) {
                $table->bigInteger('dibayarkan')->nullable()->after('total');
            }
            if (!Schema::hasColumn('transaksis', 'kembalian')) {
                $table->bigInteger('kembalian')->nullable()->after('dibayarkan');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (Schema::hasColumn('transaksis', 'kembalian')) {
                $table->dropColumn('kembalian');
            }
            if (Schema::hasColumn('transaksis', 'dibayarkan')) {
                $table->dropColumn('dibayarkan');
            }
        });
    }
};
