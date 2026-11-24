<?php

namespace Tests\Feature;

use App\Enums\JenisJadwal;
use App\Enums\StatusJadwal;
use App\Enums\StatusPengajuan;
use App\Models\Admin;
use App\Models\JadwalLayanan;
use App\Models\PaketInternet;
use App\Models\RegistrasiPelanggan;
use Database\Seeders\PaketInternetSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\RefreshDatabaseAman;
use Tests\TestCase;

/**
 * Tahap 6 — Uji integrasi: seluruh alur dijalankan lewat HTTP oleh pelanggan
 * dan admin, sesuai Activity Diagram Pengajuan Berlangganan dan Pengelolaan
 * Jadwal (Revisi 1, tanpa survei terpisah sesuai revisi mitra). Setelah setiap
 * langkah, konsistensi jadwal dan status diperiksa.
 */
class AlurLengkapTest extends TestCase
{
    use RefreshDatabaseAman;

    private const CATATAN_INTERNAL = 'Catatan internal: bawa tangga 6 meter';

    private Admin $admin;

    private PaketInternet $paket;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        (new PaketInternetSeeder)->run();
        (new SiteSettingSeeder)->run();

        $this->admin = Admin::create(['nama' => 'Admin Uji', 'username' => 'admin_uji', 'password_hash' => 'rahasia-uji-123']);
        $this->paket = PaketInternet::where('kecepatan_mbps', 25)->first();
    }

    public function test_alur_lengkap_dari_daftar_akun_sampai_terpasang(): void
    {
        // 1. Pelanggan membuat akun lalu langsung masuk.
        $this->post('/daftar', [
            'email' => 'calon@contoh.test', 'password' => 'rahasiaUji123', 'password_confirmation' => 'rahasiaUji123',
        ])->assertRedirect('/akun');
        $this->assertAuthenticated('web');

        // 2. Pelanggan memilih paket dan mengirim pengajuan.
        $this->get('/pengajuan/buat?paket='.$this->paket->id_paket)->assertOk()->assertSee($this->paket->nama_paket);
        $respons = $this->post('/pengajuan', [
            'id_paket' => $this->paket->id_paket,
            'nama_pelanggan' => 'Calon Pelanggan',
            'no_whatsapp' => '0812-3456-7890',
            'alamat_pemasangan' => 'Jl. Contoh Uji No. 10, RT 01 RW 02',
            'kecamatan' => 'Mayangan',
            'latitude' => '-7.7543', 'longitude' => '113.2159',
            'catatan' => 'Hubungi sore hari',
        ]);
        $registrasi = RegistrasiPelanggan::sole();
        $respons->assertRedirect('/pengajuan/'.$registrasi->id_registrasi);
        $this->assertSame((string) $this->paket->harga, (string) $registrasi->harga_saat_pengajuan);
        $this->periksa($registrasi, StatusPengajuan::Baru);
        $this->get('/pengajuan/'.$registrasi->id_registrasi)->assertInertia(fn (Assert $page) => $page
            ->component('Akun/Pengajuan')
            ->hasFlash('pengajuan_baru', true)
            ->where('pengajuan.nomor', $registrasi->nomor_pengajuan_pelanggan));

        // 3. Admin memverifikasi data.
        $this->sebagaiAdmin()->patch($this->urlStatus($registrasi), ['status' => 'DIVERIFIKASI'])->assertSessionHasNoErrors();
        $this->periksa($registrasi, StatusPengajuan::Diverifikasi);

        // 4. Tanpa survei terpisah: pemasangan langsung dijadwalkan, lalu jamnya diundur sekali.
        $kunjungan = $this->jadwalkan($registrasi);
        $this->periksa($registrasi, StatusPengajuan::PemasanganDijadwalkan);
        $this->get('/pengajuan/'.$registrasi->id_registrasi)
            ->assertSee(StatusPengajuan::PemasanganDijadwalkan->label())
            ->assertDontSee(self::CATATAN_INTERNAL);
        $this->put('/admin/jadwal/'.$kunjungan->id_jadwal, [
            'tanggal' => today()->toDateString(), 'waktu_mulai' => '13:00', 'catatan' => self::CATATAN_INTERNAL,
        ])->assertSessionHasNoErrors();
        $this->periksa($registrasi, StatusPengajuan::PemasanganDijadwalkan);

        // 5. Teknisi mengecek lokasi lalu memasang: "Pemasangan berhasil", dengan foto dokumentasi.
        $this->catatHasil($kunjungan, ['hasil' => 'BERHASIL', 'catatan' => 'Tiang terdekat 40 m', 'dokumentasi' => $this->fotoJpeg()]);
        $this->periksa($registrasi, StatusPengajuan::Selesai);

        // 6. Status final: tidak ada tindakan lanjutan yang diizinkan.
        $this->get('/admin/pengajuan/'.$registrasi->id_registrasi.'/jadwal/buat')->assertRedirect('/admin/pengajuan/'.$registrasi->id_registrasi);
        $this->patch($this->urlStatus($registrasi), ['status' => 'DIBATALKAN'])->assertSessionHasErrors('status');
        $this->periksa($registrasi, StatusPengajuan::Selesai);

        // 7. Pelanggan melihat statusnya tanpa catatan internal admin; pelacak 4 tahap semuanya selesai.
        $this->assertSame(1, JadwalLayanan::count());
        // Diperiksa pada props yang sudah di-decode: di HTML, JSON halaman meng-escape "/" sehingga
        // assertDontSee('/dokumentasi') akan selalu lolos.
        $halaman = $this->get('/pengajuan/'.$registrasi->id_registrasi);
        $this->assertHalamanInertia($halaman, 'Akun/Pengajuan', StatusPengajuan::Selesai->label());
        $this->assertHalamanInertiaTanpa($halaman, self::CATATAN_INTERNAL, 'Tiang terdekat 40 m', '/dokumentasi');
        $this->assertSame(
            ['selesai', 'selesai', 'selesai', 'selesai'],
            array_column($halaman->inertiaProps('pengajuan.langkah'), 'keadaan'),
        );
        $this->assertHalamanInertia($this->get('/akun'), 'Akun/Index', $registrasi->nomor_pengajuan_pelanggan, StatusPengajuan::Selesai->label());
    }

    public function test_lokasi_tidak_layak_dipasang_menghentikan_alur(): void
    {
        $registrasi = $this->pengajuanBaru();
        $this->sebagaiAdmin()->patch($this->urlStatus($registrasi), ['status' => 'DIVERIFIKASI']);
        $kunjungan = $this->jadwalkan($registrasi);

        $this->catatHasil($kunjungan, [
            'hasil' => 'TIDAK_LAYAK',
            'catatan' => 'Tiang terdekat 900 m.',
            'pesan_untuk_pelanggan' => 'Maaf, jaringan kami belum menjangkau lokasi Anda.',
        ]);
        $this->periksa($registrasi, StatusPengajuan::TidakLayak);

        // Tidak dapat dijadwalkan lagi.
        $this->get('/admin/pengajuan/'.$registrasi->id_registrasi.'/jadwal/buat')->assertRedirect('/admin/pengajuan/'.$registrasi->id_registrasi);

        // Pelanggan: berhenti di tahap "Pemasangan", membaca pesan admin, tanpa uraian internal.
        $halaman = $this->get('/pengajuan/'.$registrasi->id_registrasi);
        $this->assertSame(
            ['Pengajuan dikirim' => 'selesai', 'Pemeriksaan data' => 'selesai', 'Pemasangan' => 'berhenti', 'Terpasang' => 'belum'],
            array_column($halaman->inertiaProps('pengajuan.langkah'), 'keadaan', 'nama'),
        );
        $this->assertHalamanInertia($halaman, 'Akun/Pengajuan', 'Maaf, jaringan kami belum menjangkau lokasi Anda.');
        $this->assertHalamanInertiaTanpa($halaman, 'Tiang terdekat 900 m.');
    }

    public function test_pembatalan_di_tengah_alur_menghentikan_jadwal_aktif(): void
    {
        $registrasi = $this->pengajuanBaru();
        $this->sebagaiAdmin()->patch($this->urlStatus($registrasi), ['status' => 'DIVERIFIKASI']);
        $kunjungan = $this->jadwalkan($registrasi);

        $this->patch($this->urlStatus($registrasi), ['status' => 'DIBATALKAN'])->assertSessionHasNoErrors();

        $this->periksa($registrasi, StatusPengajuan::Dibatalkan);
        $this->assertSame(StatusJadwal::Dibatalkan, $kunjungan->fresh()->status_jadwal);
        $this->post('/admin/jadwal/'.$kunjungan->id_jadwal.'/hasil', ['hasil' => 'BERHASIL'])->assertSessionHasErrors();
        $this->assertSame(0, $kunjungan->hasil()->count());
    }

    public function test_pengajuan_ditolak_tidak_dapat_dijadwalkan(): void
    {
        $registrasi = $this->pengajuanBaru();
        $this->sebagaiAdmin()->patch($this->urlStatus($registrasi), ['status' => 'DITOLAK'])->assertSessionHasNoErrors();

        $this->post('/admin/pengajuan/'.$registrasi->id_registrasi.'/jadwal', ['jenis_jadwal' => 'PEMASANGAN', 'tanggal' => today()->toDateString(), 'waktu_mulai' => '09:00']);

        $this->periksa($registrasi, StatusPengajuan::Ditolak);
        $this->assertSame(0, JadwalLayanan::count());
    }

    // ---------- Bantuan ----------

    private function sebagaiAdmin(): static
    {
        return $this->actingAs($this->admin, 'admin');
    }

    private function urlStatus(RegistrasiPelanggan $registrasi): string
    {
        return '/admin/pengajuan/'.$registrasi->id_registrasi.'/status';
    }

    private function pengajuanBaru(): RegistrasiPelanggan
    {
        $this->post('/daftar', ['email' => 'lain@contoh.test', 'password' => 'rahasiaUji123', 'password_confirmation' => 'rahasiaUji123']);
        $this->post('/pengajuan', [
            'id_paket' => $this->paket->id_paket, 'nama_pelanggan' => 'Pemohon Lain', 'no_whatsapp' => '081234567890',
            'alamat_pemasangan' => 'Jl. Contoh Lain No. 2', 'kecamatan' => 'Kanigaran',
        ])->assertSessionHasNoErrors();

        return RegistrasiPelanggan::sole();
    }

    private function jadwalkan(RegistrasiPelanggan $registrasi): JadwalLayanan
    {
        $this->sebagaiAdmin()->get('/admin/pengajuan/'.$registrasi->id_registrasi.'/jadwal/buat')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('jenis.nilai', JenisJadwal::Pemasangan->value));
        $this->post('/admin/pengajuan/'.$registrasi->id_registrasi.'/jadwal', [
            'jenis_jadwal' => JenisJadwal::Pemasangan->value, 'tanggal' => today()->toDateString(), 'waktu_mulai' => '09:00', 'catatan' => self::CATATAN_INTERNAL,
        ])->assertSessionHasNoErrors();

        return JadwalLayanan::where('id_registrasi', $registrasi->id_registrasi)
            ->where('status_jadwal', StatusJadwal::Terjadwal->value)
            ->sole();
    }

    private function catatHasil(JadwalLayanan $jadwal, array $data): void
    {
        $this->sebagaiAdmin()->post('/admin/jadwal/'.$jadwal->id_jadwal.'/hasil', $data)->assertSessionHasNoErrors();
        $this->assertSame(StatusJadwal::Selesai, $jadwal->fresh()->status_jadwal);
    }

    /**
     * Status pengajuan sesuai harapan, dan jadwal konsisten dengan status itu:
     * - paling banyak satu jadwal TERJADWAL per pengajuan;
     * - jadwal TERJADWAL hanya ada pada status "... Dijadwalkan", dengan jenis yang cocok;
     * - setiap jadwal SELESAI memiliki tepat satu hasil, jadwal lain tidak punya hasil.
     */
    private function periksa(RegistrasiPelanggan $registrasi, StatusPengajuan $harapan): void
    {
        $registrasi->refresh();
        $this->assertSame($harapan, $registrasi->status_pengajuan);

        $jadwal = JadwalLayanan::withCount('hasil')->where('id_registrasi', $registrasi->id_registrasi)->get();
        $aktif = $jadwal->where('status_jadwal', StatusJadwal::Terjadwal);
        $jenisAktif = match ($harapan) {
            StatusPengajuan::SurveiDijadwalkan => JenisJadwal::Survei,
            StatusPengajuan::PemasanganDijadwalkan => JenisJadwal::Pemasangan,
            default => null,
        };

        $this->assertCount($jenisAktif ? 1 : 0, $aktif, 'Jumlah jadwal aktif tidak sesuai status '.$harapan->value);
        if ($jenisAktif) {
            $this->assertSame($jenisAktif, $aktif->first()->jenis_jadwal);
        }
        foreach ($jadwal as $item) {
            $this->assertSame($item->status_jadwal === StatusJadwal::Selesai ? 1 : 0, $item->hasil_count);
        }
    }

    private function fotoJpeg(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'jnet');
        file_put_contents($path, base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIfIiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCAAIAAgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwC1RRRXkn0R/9k='));

        return new UploadedFile($path, 'foto-survei.jpg', null, null, true);
    }
}
