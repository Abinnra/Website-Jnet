<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel paket_internet sesuai ERD Revisi 1.
     *
     * Paket tidak dihapus, hanya dinonaktifkan (status_aktif = false),
     * karena pengajuan lama tetap merujuk ke paket ini.
     */
    public function up(): void
    {
        Schema::create('paket_internet', function (Blueprint $table) {
            $table->id('id_paket');
            $table->string('nama_paket', 100);
            $table->unsignedInteger('kecepatan_mbps');
            $table->decimal('harga', 12, 2);
            $table->decimal('biaya_pemasangan', 12, 2)->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_internet');
    }
};
