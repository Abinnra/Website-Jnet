<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel users mengikuti ERD Website PT. JNET (Revisi 1):
     * id_user, email, password_hash, created_at, updated_at.
     *
     * Tabel password_reset_tokens bawaan Laravel tidak dibuat karena
     * tidak ada use case lupa kata sandi.
     *
     * Tabel sessions adalah infrastruktur Laravel (SESSION_DRIVER=database)
     * dan dipertahankan tanpa perubahan.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
