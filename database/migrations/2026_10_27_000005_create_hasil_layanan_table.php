<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel hasil_layanan sesuai ERD Revisi 1.
     *
     * - id_jadwal UNIQUE: satu jadwal memiliki paling banyak satu hasil
     *   (relasi 1 : 0..1).
     * - hasil berisi kode keputusan (LAYAK, TIDAK_LAYAK, SURVEI_ULANG,
     *   BERHASIL, PEMASANGAN_ULANG); uraian disimpan di catatan.
     * - dokumentasi_url berisi path relatif file di disk privat.
     * - Hanya created_at (tanpa updated_at), sesuai ERD.
     */
    public function up(): void
    {
        Schema::create('hasil_layanan', function (Blueprint $table) {
            $table->id('id_hasil');

            $table->foreignId('id_jadwal')
                ->unique()
                ->constrained('jadwal_layanan', 'id_jadwal')
                ->restrictOnDelete();

            $table->foreignId('id_admin')
                ->constrained('admins', 'id_admin')
                ->restrictOnDelete();

            $table->text('hasil');
            $table->text('dokumentasi_url')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_layanan');
    }
};
