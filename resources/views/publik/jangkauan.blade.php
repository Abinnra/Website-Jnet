<x-layouts.publik judul="Area Jangkauan" deskripsi="Wilayah layanan internet JNET: Kota dan Kabupaten Probolinggo sebagai wilayah utama, serta pengajuan dari luar Probolinggo."
                  :kepala-transparan="true">
    @php
        $tautanWa = $situs->tautanWhatsapp('Halo JNET, apakah layanan tersedia di alamat saya? Alamat: ');
        // Titik pusat animasi = titik acuan wilayah (config jnet.wilayah.titik_acuan).
        $titikPusat = \App\Support\Wilayah::titikAcuan();
        $versiAset = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
    @endphp

    <x-kepala-halaman class="kepala-halaman--peta" judul="Area jangkauan"
                      lead="Wilayah utama JNET adalah Kota dan Kabupaten Probolinggo, Jawa Timur. Lokasi di luar Probolinggo juga dapat mengajukan pemasangan.">
        <x-slot:samping>
            {{-- Animasi hiasan: bumi berputar ke Jawa, lalu area Probolinggo menyala dan terhubung --}}
            <div class="peta-bumi">
                <canvas class="peta-bumi__kanvas" data-peta-jangkauan aria-hidden="true"
                        data-label="Probolinggo"
                        @if ($titikPusat) data-lintang="{{ $titikPusat['latitude'] }}" data-bujur="{{ $titikPusat['longitude'] }}" @endif></canvas>
            </div>
        </x-slot:samping>
    </x-kepala-halaman>

    <section class="uk-section">
        <div class="uk-container">
            <div class="jangkauan uk-grid uk-grid-large" uk-grid>
            <div class="uk-width-1-1 uk-width-2-5@m jangkauan__penjelasan">
                <h2>Ketersediaan ditentukan oleh alamat</h2>
                <p>
                    Tidak semua titik di setiap kecamatan dapat langsung dipasang. Ketersediaan layanan
                    dipastikan berdasarkan alamat pemasangan dan pengecekan lokasi oleh teknisi kami saat pemasangan.
                </p>
                @if ($situs->ada('info_jangkauan'))
                    <p>{!! nl2br(e($situs->get('info_jangkauan'))) !!}</p>
                @endif

                <h2>Berada di luar Probolinggo?</h2>
                <p>
                    Anda tetap dapat mengajukan pemasangan. Pilih "Di luar Probolinggo" pada formulir dan tandai
                    titik lokasi di peta. Admin JNET akan mengonfirmasi apakah lokasi Anda dapat dijangkau
                    sebelum pemasangan dijadwalkan.
                </p>

                <div class="jangkauan__aksi">
                    @if ($tautanWa)
                        <a class="uk-button uk-button-primary" href="{{ $tautanWa }}" target="_blank" rel="noopener">Tanyakan alamat Anda</a>
                    @endif
                    <a class="uk-button uk-button-default" href="{{ route('alur') }}">Lihat alur berlangganan</a>
                </div>
            </div>

            <div class="uk-width-1-1 uk-width-3-5@m jangkauan__wilayah">
                <p class="jangkauan__keterangan">Wilayah utama layanan, yang kecamatannya dapat langsung dipilih saat mengajukan pemasangan:</p>
                @foreach ($wilayah as $namaWilayah => $daftarKecamatan)
                    <div class="wilayah">
                        <h3 class="wilayah__judul">{{ $namaWilayah }} <span>{{ count($daftarKecamatan) }} kecamatan</span></h3>
                        <ul class="wilayah__daftar">
                            @foreach ($daftarKecamatan as $kecamatan)
                                <li>{{ $kecamatan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    </section>
    <script src="{{ $versiAset('js/peta-jangkauan.js') }}" defer></script>
</x-layouts.publik>
