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
        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_pengeluaran'); // Jenis pengeluaran (contoh: Transport, Operasional, dll)
            $table->decimal('nominal', 15, 2); // Nominal pengeluaran
            $table->text('keterangan')->nullable(); // Keterangan tambahan
            $table->date('tanggal'); // Tanggal pengeluaran
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengeluarans');
    }
};
