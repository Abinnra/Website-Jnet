<x-layouts.publik judul="Alur Berlangganan" deskripsi="Tahapan berlangganan internet JNET dari pengajuan sampai pemasangan."
                  :kepala-transparan="true">
    <x-kepala-halaman class="kepala-halaman--rute" judul="Alur berlangganan"
                      lead="Dari pengajuan sampai internet terpasang, setiap tahap dapat Anda pantau dari akun Anda.">
        <ul class="sorot-angka" aria-label="Ringkasan alur">
            <li><strong>{{ count(\App\Support\AlurLangganan::langkah()) }}</strong> tahap</li>
            <li>Ajukan <strong>online</strong></li>
            <li>Pantau status dari <strong>akun Anda</strong></li>
        </ul>

        <x-slot:samping>
            {{-- Rute tahapan: cahaya mengalir dari tahap pertama sampai pemasangan selesai (hiasan) --}}
            <div class="panel-kaca">
                <p class="panel-kaca__judul">Perjalanan pengajuan Anda</p>
                <ol class="rute" style="--n: {{ count(\App\Support\AlurLangganan::langkah()) }}">
                    @foreach (\App\Support\AlurLangganan::langkah() as [$judulTahap])
                        <li class="rute__item" style="--i: {{ $loop->index }}">
                            <span class="rute__titik" aria-hidden="true">
                                @if ($loop->last)
                                    {{-- Tahap terakhir menyala hijau dan centangnya tergambar: tanda pemasangan berhasil --}}
                                    <x-ikon nama="centang-garis" />
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            <span class="rute__judul">{{ $judulTahap }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </x-slot:samping>
    </x-kepala-halaman>

    <section class="uk-section">
        <div class="uk-container">
            <x-langkah-alur :level="2" />

            <div class="panduan-singkat">
                <h2>Yang perlu disiapkan</h2>
                <ul>
                    <li>Alamat email untuk membuat akun.</li>
                    <li>Nomor WhatsApp yang aktif agar tim kami dapat menghubungi Anda.</li>
                    <li>Alamat pemasangan yang lengkap. Titik lokasi di peta bersifat opsional untuk Kota dan Kabupaten Probolinggo, tetapi membantu teknisi menemukan lokasi.</li>
                    <li>Untuk lokasi di luar Probolinggo, titik lokasi di peta wajib diisi. Admin akan mengonfirmasi jangkauannya sebelum pemasangan dijadwalkan.</li>
                </ul>
            </div>

            <div class="bagian__aksi">
                @if (Route::has('pengajuan.create'))
                    <a class="uk-button uk-button-primary" href="{{ route('pengajuan.create') }}">Ajukan pemasangan</a>
                @endif
                <a class="uk-button {{ Route::has('pengajuan.create') ? 'uk-button-default' : 'uk-button-primary' }}" href="{{ route('paket') }}">Lihat paket</a>
            </div>
        </div>
    </section>
</x-layouts.publik>
