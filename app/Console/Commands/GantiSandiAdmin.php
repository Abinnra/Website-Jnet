<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Mengganti kata sandi admin dari terminal.
 *
 * Kata sandi diketik tersembunyi (tidak tampil di layar), tidak disimpan di
 * .env, dan langsung disimpan sebagai hash di tabel admins.
 *
 * Contoh: php artisan admin:ganti-sandi
 *         php artisan admin:ganti-sandi taraa123
 */
class GantiSandiAdmin extends Command
{
    protected $signature = 'admin:ganti-sandi {username? : Username admin yang kata sandinya diganti}';

    protected $description = 'Mengganti kata sandi akun admin JNET (diketik tersembunyi, disimpan sebagai hash)';

    private const BATAS_PERCOBAAN = 3;

    public function handle(): int
    {
        $admin = $this->pilihAdmin();

        if (! $admin) {
            return self::FAILURE;
        }

        $this->info("Mengganti kata sandi untuk {$admin->nama} ({$admin->username}).");

        for ($percobaan = 1; $percobaan <= self::BATAS_PERCOBAAN; $percobaan++) {
            $sandi = (string) $this->secret('Kata sandi baru (minimal 8 karakter, berisi huruf dan angka)');
            $ulang = (string) $this->secret('Ulangi kata sandi baru');

            $kesalahan = $this->periksa($admin, $sandi, $ulang);

            if ($kesalahan === []) {
                $admin->password_hash = $sandi; // Di-hash otomatis oleh cast 'hashed' pada model.
                $admin->save();

                $this->info('Kata sandi berhasil diganti. Gunakan kata sandi baru saat masuk di /admin/masuk.');

                return self::SUCCESS;
            }

            foreach ($kesalahan as $pesan) {
                $this->error($pesan);
            }
        }

        $this->error('Gagal '.self::BATAS_PERCOBAAN.' kali. Kata sandi tidak diubah.');

        return self::FAILURE;
    }

    private function pilihAdmin(): ?Admin
    {
        $username = $this->argument('username');

        if ($username === null) {
            $daftar = Admin::query()->orderBy('username')->pluck('username')->all();

            if ($daftar === []) {
                $this->error('Belum ada akun admin. Isi JNET_ADMIN_* di .env lalu jalankan php artisan db:seed --class=AdminSeeder.');

                return null;
            }

            $username = count($daftar) === 1 ? $daftar[0] : $this->choice('Pilih username admin', $daftar);
        }

        $admin = Admin::query()->where('username', $username)->first();

        if (! $admin) {
            $this->error('Admin dengan username tersebut tidak ditemukan.');
        }

        return $admin;
    }

    /**
     * @return list<string>
     */
    private function periksa(Admin $admin, string $sandi, string $ulang): array
    {
        $validator = Validator::make(
            ['password' => $sandi, 'password_confirmation' => $ulang],
            ['password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed']],
            [],
            ['password' => 'kata sandi'],
        );

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        if (Hash::check($sandi, $admin->password_hash)) {
            return ['Kata sandi baru tidak boleh sama dengan kata sandi lama.'];
        }

        return [];
    }
}
