/*
 * Formulir pengajuan model wizard 4 langkah (revisi mitra: memakai model website
 * alternatif 3):
 *   1. Paket  2. Data diri  3. Lokasi  4. Periksa & kirim.
 *
 * Isian setiap langkah diperiksa di browser sebelum lanjut, dengan aturan dan
 * pesan yang sama seperti StorePengajuanRequest. Server tetap memvalidasi
 * semuanya. Tanpa JavaScript semua langkah tampil berurutan dalam satu halaman
 * dan formulir tetap dapat dikirim.
 *
 * Peta memakai Leaflet + OpenStreetMap (gratis, tanpa API key). Jika peta atau
 * lokasi browser tidak tersedia, pengajuan di Probolinggo tetap dapat dikirim
 * tanpa koordinat.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-wizard]');
    if (!form) {
        return;
    }

    const $ = (selektor, induk = form) => induk.querySelector(selektor);
    const $$ = (selektor, induk = form) => Array.from(induk.querySelectorAll(selektor));
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

    const inputLat = $('[data-peta-lat]');
    const inputLng = $('[data-peta-lng]');

    // Fungsi yang dijalankan saat sebuah langkah ditampilkan (misalnya mengukur ulang peta).
    const saatTampil = { 1: [], 2: [], 3: [], 4: [] };

    // ---------- Wilayah (Revisi 2) ----------
    // Hanya isian untuk wilayah terpilih yang tampil dan dikirim.
    const wilayahTerpilih = () => {
        const terpilih = $('input[name="wilayah"]:checked');
        return terpilih && terpilih.value === 'luar' ? 'luar' : 'probolinggo';
    };

    const aturWilayah = () => {
        const wilayah = wilayahTerpilih();
        $$('[data-wilayah-grup]').forEach((grup) => {
            const aktif = grup.dataset.wilayahGrup === wilayah;
            grup.hidden = !aktif;
            $$('input, select', grup).forEach((isian) => { isian.disabled = !aktif; });
        });

        const labelOpsional = $('[data-label-peta-probolinggo]');
        const labelWajib = $('[data-label-peta-luar]');
        if (labelOpsional && labelWajib) {
            labelOpsional.hidden = wilayah === 'luar';
            labelWajib.hidden = wilayah !== 'luar';
        }
    };
    $$('input[name="wilayah"]').forEach((pilihan) => pilihan.addEventListener('change', aturWilayah));
    aturWilayah();

    // ---------- Paket terpilih ----------
    const pilihanPaket = $$('input[name="id_paket"]');
    const paketTerpilih = () => $('input[name="id_paket"]:checked');

    const perbaruiPaket = () => {
        const terpilih = paketTerpilih();
        if (terpilih) {
            $('[data-ringkasan-paket]').textContent = terpilih.dataset.nama;
            $('[data-ringkasan-harga]').textContent = terpilih.dataset.harga;
        }
    };
    pilihanPaket.forEach((pilihan) => pilihan.addEventListener('change', perbaruiPaket));
    perbaruiPaket();

    // ---------- Aturan per langkah (sama dengan StorePengajuanRequest) ----------
    const nilai = (nama) => {
        const isian = form.elements.namedItem(nama);
        return isian && !isian.disabled ? String(isian.value || '').trim() : '';
    };

    // Sama dengan App\Support\Format::nomorWhatsapp: 0812-... / +62 812... -> 62812...
    const nomorWhatsappValid = (nomor) => {
        let angka = nomor.replace(/\D+/g, '');
        if (angka.startsWith('0')) {
            angka = `62${angka.slice(1)}`;
        } else if (angka.startsWith('8')) {
            angka = `62${angka}`;
        }
        return /^628\d{7,11}$/.test(angka);
    };

    const wajibDanMinimal = (nama, pesanKosong, minimal, pesanPendek) => () => {
        const isi = nilai(nama);
        if (isi === '') return pesanKosong;
        if (isi.length < minimal) return pesanPendek;
        return '';
    };

    const aturan = {
        1: {
            id_paket: () => (paketTerpilih() ? '' : 'Pilih salah satu paket.'),
        },
        2: {
            nama_pelanggan: wajibDanMinimal('nama_pelanggan', 'Nama pemohon wajib diisi.', 3, 'Nama pemohon minimal 3 karakter.'),
            no_whatsapp: () => {
                const nomor = nilai('no_whatsapp');
                if (nomor === '') return 'Nomor WhatsApp wajib diisi.';
                if (!nomorWhatsappValid(nomor)) return 'Nomor WhatsApp tidak valid. Contoh: 0812 3456 7890.';
                return '';
            },
        },
        3: {
            alamat_pemasangan: wajibDanMinimal('alamat_pemasangan', 'Alamat pemasangan wajib diisi.', 10,
                'Tulis alamat pemasangan lebih lengkap (minimal 10 karakter).'),
            kecamatan: () => (wilayahTerpilih() === 'probolinggo' && nilai('kecamatan') === '' ? 'Pilih kecamatan dari daftar.' : ''),
            kota_kabupaten: () => (wilayahTerpilih() !== 'luar' ? ''
                : wajibDanMinimal('kota_kabupaten', 'Tulis nama kabupaten atau kota, misalnya Kabupaten Lumajang.', 3,
                    'Kabupaten/kota minimal 3 karakter.')()),
            kecamatan_luar: () => (wilayahTerpilih() !== 'luar' ? ''
                : wajibDanMinimal('kecamatan_luar', 'Tulis nama kecamatan lokasi pemasangan.', 3, 'Kecamatan minimal 3 karakter.')()),
            latitude: () => (wilayahTerpilih() === 'luar' && inputLat.value === ''
                ? 'Untuk lokasi di luar Probolinggo, tandai titik lokasi di peta agar admin dapat menilai jangkauannya.'
                : ''),
        },
    };

    // ---------- Pesan galat di bawah isian (gaya sama seperti galat dari server) ----------
    // Mengembalikan elemen yang difokuskan jika isian ini salah.
    const tandaiGalat = (nama, pesan) => {
        const salah = pesan !== '';
        const galat = document.createElement('p');
        galat.className = 'field__error';
        galat.textContent = pesan;

        if (nama === 'id_paket') {
            const grup = $('.pilihan-paket');
            galat.id = 'error-id_paket';
            grup.classList.toggle('pilihan-paket--error', salah);
            $$('#error-id_paket').forEach((lama) => lama.remove());
            if (salah) {
                grup.setAttribute('aria-describedby', galat.id);
                grup.after(galat);
            } else {
                grup.removeAttribute('aria-describedby');
            }
            return paketTerpilih() || pilihanPaket[0];
        }

        if (nama === 'latitude') {
            const wadah = $('[data-field-peta]');
            galat.id = 'error-latitude';
            wadah.classList.toggle('field--error', salah);
            $$('.field__error', wadah).forEach((lama) => lama.remove());
            if (salah) {
                wadah.append(galat);
            }
            return $('.leaflet-container[tabindex]', wadah) || $('[data-peta-lokasi-saya]', wadah);
        }

        const isian = form.elements.namedItem(nama);
        const wadah = isian.closest('.field');
        const bantuan = $('.field__bantuan', wadah);
        galat.id = `field-${nama}-error`;

        wadah.classList.toggle('field--error', salah);
        // Galat lama (dari server atau pemeriksaan sebelumnya) diganti agar tidak dobel.
        $$('.field__error', wadah).forEach((lama) => lama.remove());
        if (salah) {
            wadah.insertBefore(galat, bantuan);
            isian.setAttribute('aria-invalid', 'true');
        } else {
            isian.removeAttribute('aria-invalid');
        }

        const terkait = [bantuan ? bantuan.id : '', salah ? galat.id : ''].filter(Boolean).join(' ');
        if (terkait) {
            isian.setAttribute('aria-describedby', terkait);
        } else {
            isian.removeAttribute('aria-describedby');
        }
        return isian;
    };

    // Memeriksa satu langkah; mengembalikan isian pertama yang salah, atau null.
    const periksaLangkah = (nomor) => {
        let isianPertamaSalah = null;
        Object.entries(aturan[nomor] || {}).forEach(([nama, cek]) => {
            const pesan = cek();
            const isian = tandaiGalat(nama, pesan);
            if (pesan !== '' && !isianPertamaSalah) {
                isianPertamaSalah = isian;
            }
        });
        return isianPertamaSalah;
    };

    // Setelah ditandai salah, galat hilang sendiri begitu isian itu diperbaiki.
    // Hanya isian yang diubah yang diperiksa ulang, agar galat lain dari server tetap tampil.
    const sedangSalah = (nama) => {
        if (nama === 'id_paket') return $('.pilihan-paket').classList.contains('pilihan-paket--error');
        if (nama === 'latitude') return $('[data-field-peta]').classList.contains('field--error');
        return form.elements.namedItem(nama).closest('.field').classList.contains('field--error');
    };
    const cekUntuk = (nama) => Object.values(aturan).find((daftar) => daftar[nama])[nama];
    const periksaUlang = (...daftarNama) => () => {
        daftarNama.forEach((nama) => {
            if (sedangSalah(nama)) {
                tandaiGalat(nama, cekUntuk(nama)());
            }
        });
    };
    pilihanPaket.forEach((pilihan) => pilihan.addEventListener('change', periksaUlang('id_paket')));
    ['nama_pelanggan', 'no_whatsapp', 'alamat_pemasangan', 'kota_kabupaten', 'kecamatan_luar'].forEach((nama) => {
        form.elements.namedItem(nama).addEventListener('input', periksaUlang(nama));
    });
    form.elements.namedItem('kecamatan').addEventListener('change', periksaUlang('kecamatan'));
    // Isian yang wajib bergantung pada wilayah, jadi diperiksa ulang saat wilayah diganti.
    $$('input[name="wilayah"]').forEach((pilihan) => {
        pilihan.addEventListener('change', periksaUlang('kecamatan', 'kota_kabupaten', 'kecamatan_luar', 'latitude'));
    });

    // ---------- Ringkasan "Periksa dan kirim" ----------
    const isiPeriksa = () => {
        const daftar = $('[data-periksa]');
        const tulis = (kunci, teks) => { $(`[data-periksa-isi="${kunci}"]`, daftar).textContent = teks; };
        const terpilih = paketTerpilih();

        tulis('paket', terpilih ? `${terpilih.dataset.nama} · ${terpilih.dataset.harga}` : 'Belum dipilih');
        tulis('pemohon', [nilai('nama_pelanggan'), nilai('no_whatsapp')].filter(Boolean).join(' · ') || '–');

        let daerah;
        if (wilayahTerpilih() === 'luar') {
            daerah = [nilai('kecamatan_luar'), nilai('kota_kabupaten')];
        } else {
            const opsi = form.elements.namedItem('kecamatan').selectedOptions[0];
            daerah = [nilai('kecamatan'), opsi && opsi.parentElement.tagName === 'OPTGROUP' ? opsi.parentElement.label : ''];
        }
        const titik = inputLat.value !== '' ? 'Titik lokasi ditandai' : 'Tanpa titik lokasi';
        tulis('lokasi', [nilai('alamat_pemasangan'), daerah.filter(Boolean).join(', '), titik].filter(Boolean).join(' · '));
        daftar.hidden = false;
    };

    // ---------- Perpindahan langkah ----------
    const daftarLangkah = $$('[data-langkah]');
    const progres = $$('[data-progres]');
    const tombolKembali = $('[data-wizard-kembali]');
    const tombolLanjut = $('[data-wizard-lanjut]');
    const tombolKirim = $('[data-wizard-kirim]');
    const barisPilihan = $('[data-wizard-pilihan]');
    const TERAKHIR = daftarLangkah.length;
    let sekarang = 1;

    form.classList.add('wizard--aktif');

    const tampilkan = (nomor, gulir = true) => {
        sekarang = Math.min(Math.max(nomor, 1), TERAKHIR);

        daftarLangkah.forEach((bagian) => { bagian.hidden = Number(bagian.dataset.langkah) !== sekarang; });
        progres.forEach((item) => {
            const urutan = Number(item.dataset.progres);
            item.classList.toggle('wizard__progres-item--selesai', urutan < sekarang);
            item.classList.toggle('wizard__progres-item--aktif', urutan === sekarang);
            if (urutan === sekarang) {
                item.setAttribute('aria-current', 'step');
            } else {
                item.removeAttribute('aria-current');
            }
        });

        tombolKembali.hidden = sekarang === 1;
        tombolLanjut.hidden = sekarang === TERAKHIR;
        tombolKirim.hidden = sekarang !== TERAKHIR;
        barisPilihan.hidden = sekarang === 1 || !paketTerpilih();

        if (sekarang === TERAKHIR) {
            isiPeriksa();
        }
        saatTampil[sekarang].forEach((fungsi) => fungsi());

        if (gulir) {
            form.scrollIntoView({ behavior: kurangiGerak.matches ? 'auto' : 'smooth', block: 'start' });
            const judul = $(`[data-langkah="${sekarang}"] .wizard__judul`);
            judul.setAttribute('tabindex', '-1');
            judul.focus({ preventScroll: true });
        }
    };

    const fokus = (isian) => {
        if (isian && typeof isian.focus === 'function') {
            isian.focus();
        }
    };

    const lanjut = () => {
        const isianSalah = periksaLangkah(sekarang);
        if (isianSalah) {
            fokus(isianSalah);
            return;
        }
        tampilkan(sekarang + 1);
    };

    tombolLanjut.addEventListener('click', lanjut);
    tombolKembali.addEventListener('click', () => tampilkan(sekarang - 1));
    $$('[data-ke-langkah]').forEach((tombol) => {
        tombol.addEventListener('click', () => tampilkan(Number(tombol.dataset.keLangkah)));
    });

    // Enter di kolom isian berarti "Lanjut", bukan kirim, sebelum langkah terakhir.
    // Enter yang sedang mengonfirmasi rangkaian huruf (IME, sebagian keyboard HP) bukan perintah
    // "Lanjut": isComposing, atau keyCode 229 untuk Safari yang urutan peristiwanya terbalik.
    form.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.tagName === 'INPUT' && sekarang < TERAKHIR) {
            event.preventDefault();
            if (event.isComposing || event.keyCode === 229) {
                return;
            }
            lanjut();
        }
    });

    // Sebelum dikirim, langkah 1–3 diperiksa lagi (mungkin diubah setelah kembali).
    // Dipasang di fase capture agar berjalan sebelum app.js menonaktifkan tombol kirim.
    form.addEventListener('submit', (event) => {
        if (sekarang < TERAKHIR) {
            event.preventDefault();
            event.stopImmediatePropagation();
            lanjut();
            return;
        }
        for (let nomor = 1; nomor < TERAKHIR; nomor += 1) {
            const isianSalah = periksaLangkah(nomor);
            if (isianSalah) {
                event.preventDefault();
                event.stopImmediatePropagation();
                tampilkan(nomor);
                fokus(isianSalah);
                return;
            }
        }
    }, true);

    // Langkah awal: langkah yang berisi galat dari server; jika paket sudah dipilih
    // dari halaman Paket (?paket=ID), langsung mulai dari data diri.
    const awal = Number(form.dataset.langkahAwal) || (paketTerpilih() ? 2 : 1);
    tampilkan(awal, false);

    // ---------- Peta titik lokasi ----------
    const wadah = $('[data-peta]');
    if (!wadah) {
        return;
    }

    const kanvas = $('[data-peta-kanvas]', wadah);
    const status = $('[data-peta-status]', wadah);
    const tombolLokasi = $('[data-peta-lokasi-saya]', wadah);
    const tombolHapus = $('[data-peta-hapus]', wadah);
    const PUSAT_PROBOLINGGO = [-7.7543, 113.2159];
    const periksaUlangLokasi = periksaUlang('latitude');

    let peta = null;
    let penanda = null;

    const tulis = (teks) => { status.textContent = teks; };
    const bulatkan = (angka) => Number(angka).toFixed(7);

    const pasangTitik = (lat, lng, geserPeta) => {
        inputLat.value = bulatkan(lat);
        inputLng.value = bulatkan(lng);
        tombolHapus.hidden = false;

        if (peta) {
            if (!penanda) {
                penanda = window.L.marker([lat, lng], { draggable: true, title: 'Titik lokasi pemasangan' }).addTo(peta);
                penanda.on('dragend', (event) => {
                    const posisi = event.target.getLatLng();
                    pasangTitik(posisi.lat, posisi.lng, false);
                });
            } else {
                penanda.setLatLng([lat, lng]);
            }
            if (geserPeta) {
                peta.setView([lat, lng], Math.max(peta.getZoom(), 17));
            }
        }

        tulis(`Titik dipilih: ${bulatkan(lat)}, ${bulatkan(lng)}. Pastikan titik sesuai lokasi pemasangan, bukan lokasi Anda saat ini.`);
        periksaUlangLokasi();
    };

    const hapusTitik = () => {
        inputLat.value = '';
        inputLng.value = '';
        if (penanda) {
            peta.removeLayer(penanda);
            penanda = null;
        }
        tombolHapus.hidden = true;
        tulis('Titik lokasi dihapus.');
        periksaUlangLokasi();
    };

    if (window.L) {
        peta = window.L.map(kanvas, { scrollWheelZoom: false }).setView(PUSAT_PROBOLINGGO, 12);
        window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(peta);
        peta.on('click', (event) => pasangTitik(event.latlng.lat, event.latlng.lng, false));
        tulis('Klik peta untuk menandai lokasi pemasangan.');

        // Peta yang dibuat saat langkah 3 masih tersembunyi berukuran 0, jadi ukurannya
        // dihitung ulang saat tampil, lalu dipusatkan ke titik terpilih (atau Probolinggo).
        let sudahDipusatkan = sekarang === 3;
        const ukurUlang = () => {
            peta.invalidateSize(false);
            if (!sudahDipusatkan) {
                sudahDipusatkan = true;
                peta.setView(penanda ? penanda.getLatLng() : PUSAT_PROBOLINGGO, peta.getZoom());
            }
        };
        saatTampil[3].push(ukurUlang);
        if (sekarang === 3) {
            ukurUlang();
        }
    } else {
        kanvas.hidden = true;
        tulis('Peta tidak dapat dimuat (periksa koneksi internet). Untuk lokasi di Probolinggo, pengajuan tetap dapat dikirim tanpa titik lokasi.');
    }

    // Kembalikan titik setelah validasi server gagal.
    if (inputLat.value !== '' && inputLng.value !== '') {
        pasangTitik(inputLat.value, inputLng.value, true);
    }

    tombolHapus.addEventListener('click', hapusTitik);

    // Lokasi browser hanya tersedia di https atau localhost/127.0.0.1.
    if (!('geolocation' in navigator) || !window.isSecureContext) {
        tombolLokasi.hidden = true;
        return;
    }

    tombolLokasi.addEventListener('click', () => {
        tombolLokasi.disabled = true;
        tulis('Mencari lokasi Anda…');

        navigator.geolocation.getCurrentPosition(
            (posisi) => {
                tombolLokasi.disabled = false;
                pasangTitik(posisi.coords.latitude, posisi.coords.longitude, true);
                tulis(`Lokasi ditemukan (akurasi sekitar ${Math.round(posisi.coords.accuracy)} meter). Geser penanda jika belum tepat di lokasi pemasangan.`);
            },
            (galat) => {
                tombolLokasi.disabled = false;
                const pesan = {
                    1: 'Izin lokasi ditolak. Izinkan akses lokasi di pengaturan browser, atau klik peta secara manual.',
                    2: 'Lokasi tidak dapat ditentukan. Klik peta secara manual.',
                    3: 'Pencarian lokasi terlalu lama. Coba lagi atau klik peta secara manual.',
                };
                tulis(pesan[galat.code] || 'Lokasi tidak dapat ditentukan. Klik peta secara manual.');
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
        );
    });
});
