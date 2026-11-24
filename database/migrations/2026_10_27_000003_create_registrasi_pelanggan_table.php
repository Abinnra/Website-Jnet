<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel registrasi_pelanggan sesuai ERD Revisi 1.
     *
     * - harga_saat_pengajuan nullable sesuai ERD, tetapi selalu diisi sistem.
     * - updated_at ditambahkan pada Revisi 1.
     * - Foreign key ke users dan paket_internet memakai RESTRICT agar data
     *   pengajuan tidak kehilangan rujukan.
     */
    public function up(): void
    {
        Schema::create('registrasi_pelanggan', function (Blueprint $table) {
            $table->id('id_registrasi');

            $table->foreignId('id_user')
                ->constrained('users', 'id_user')
                ->restrictOnDelete();

            $table->foreignId('id_paket')
                ->constrained('paket_internet', 'id_paket')
                ->restrictOnDelete();

            $table->string('nama_pelanggan', 100);
            $table->string('no_whatsapp', 20);
            $table->text('alamat_pemasangan');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('kecamatan', 50);
            $table->text('catatan')->nullable();
            $table->decimal('harga_saat_pengajuan', 12, 2)->nullable();
            $table->string('status_pengajuan', 30)->default('BARU')->index();
            $table->timestamps();

            $table->foreignId('updated_by_admin_id')
                ->nullable()
                ->constrained('admins', 'id_admin')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrasi_pelanggan');
    }
};
