<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel jadwal_layanan sesuai ERD Revisi 1.
     *
     * - jenis_jadwal: ENUM SURVEI atau PEMASANGAN.
     * - status_jadwal: TERJADWAL, SELESAI, DIBATALKAN.
     * - updated_at ditambahkan pada Revisi 1.
     * - Index (status_jadwal, tanggal) untuk daftar jadwal di dashboard.
     */
    public function up(): void
    {
        Schema::create('jadwal_layanan', function (Blueprint $table) {
            $table->id('id_jadwal');

            $table->foreignId('id_registrasi')
                ->constrained('registrasi_pelanggan', 'id_registrasi')
                ->restrictOnDelete();

            $table->foreignId('id_admin')
                ->nullable()
                ->constrained('admins', 'id_admin')
                ->nullOnDelete();

            $table->enum('jenis_jadwal', ['SURVEI', 'PEMASANGAN']);
            $table->date('tanggal');
            $table->time('waktu_mulai');
            $table->string('status_jadwal', 20)->default('TERJADWAL');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['status_jadwal', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_layanan');
    }
};
