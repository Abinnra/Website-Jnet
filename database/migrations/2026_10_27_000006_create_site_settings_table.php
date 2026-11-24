<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel site_settings sesuai ERD Revisi 1.
     *
     * - Hanya kunci yang terdaftar di config/jnet.php yang dapat diubah admin.
     * - nilai_setting wajib terisi; string kosong berarti belum tersedia.
     * - Hanya updated_at (tanpa created_at), sesuai ERD.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id('id_setting');
            $table->string('nama_setting', 100)->unique();
            $table->text('nilai_setting');

            $table->foreignId('id_admin')
                ->nullable()
                ->constrained('admins', 'id_admin')
                ->nullOnDelete();

            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
