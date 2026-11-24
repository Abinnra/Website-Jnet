/*
 * Komponen geser (carousel) untuk banner beranda dan etalase paket.
 *
 * Dasarnya CSS scroll-snap: tanpa JavaScript, isi tetap dapat digeser dengan
 * jari, trackpad, atau scrollbar. Skrip ini menambah tombol panah, titik
 * halaman, dan pergantian otomatis (hanya jika data-geser-otomatis diisi) yang:
 * - berhenti sementara saat fokus keyboard berada di dalamnya atau kursor mouse di atasnya,
 * - dapat dijeda dengan tombol data-geser-jeda (opsional; banner beranda tidak memakainya),
 * - tidak berjalan sendiri jika pengguna memilih "kurangi animasi" (pergeseran manual juga tanpa efek geser),
 * - menghitung ulang jedanya setelah pengunjung menggeser sendiri dengan jari, trackpad, atau roda tetikus.
 * Pola carousel WAI (ditemukan saat tinjauan skill UI/UX Pro Max).
 *
 * Atribut:
 *   data-geser                akar komponen
 *   data-geser-trek           wadah yang digeser
 *   data-geser-item           setiap item
 *   data-geser-sebelum/-berikut  tombol panah
 *   data-geser-titik          wadah tombol titik (diisi skrip)
 *   data-geser-jeda           tombol jeda/putar (opsional)
 *   data-geser-kendali        wadah kendali (disembunyikan jika cukup satu halaman)
 *   data-geser-putar          tanpa ujung (untuk satu item per halaman, misalnya banner):
 *                             setelah item terakhir, "berikutnya" tetap bergeser ke kanan menuju
 *                             item pertama (tidak mundur melewati semua item), begitu juga sebaliknya.
 *                             Caranya: item pertama dan terakhir disalin ke kedua ujung trek; setelah
 *                             tiba di salinan, posisi dipindah diam-diam ke item aslinya (tampilannya sama).
 *   data-geser-otomatis="7"   pergantian otomatis tiap 7 detik
 *   data-geser-nama="Banner"  nama untuk label titik, misalnya "Banner 2 dari 4"
 *   data-geser-seret          dapat diseret dengan kursor mouse (jari dan trackpad sudah bisa secara bawaan)
 */
(() => {
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

    const pasang = (akar) => {
        const trek = akar.querySelector('[data-geser-trek]');
        const daftarItem = trek ? [...trek.querySelectorAll('[data-geser-item]')] : [];

        if (daftarItem.length === 0) {
            return;
        }

        const tombolSebelum = akar.querySelector('[data-geser-sebelum]');
        const tombolBerikut = akar.querySelector('[data-geser-berikut]');
        const wadahTitik = akar.querySelector('[data-geser-titik]');
        const tombolJeda = akar.querySelector('[data-geser-jeda]');
        const wadahKendali = akar.querySelector('[data-geser-kendali]');
        const putar = akar.hasAttribute('data-geser-putar');
        const detikOtomatis = Number(akar.dataset.geserOtomatis || 0);
        const nama = akar.dataset.geserNama || 'Halaman';

        let perHalaman = 1;
        let jumlahHalaman = 1;
        let halaman = 0;
        let pewaktu = null;
        let ditahan = false; // fokus keyboard di dalam carousel
        let disorot = false; // kursor mouse di atas carousel
        let dijeda = kurangiGerak.matches; // "kurangi animasi": tidak berganti sendiri
        let sedangDiseret = false; // trek sedang diseret dengan kursor mouse (data-geser-seret)

        // Jarak antar-item (lebar item + celah).
        const langkah = () => (daftarItem.length > 1
            ? daftarItem[1].offsetLeft - daftarItem[0].offsetLeft
            : trek.clientWidth) || 1;

        // Posisi gulir sebuah elemen di dalam trek (dihitung dari elemen pertama, termasuk salinan).
        const posisiItem = (elemen) => elemen.offsetLeft - trek.firstElementChild.offsetLeft;

        // Pindah posisi seketika (tanpa animasi), misalnya dari salinan ke item aslinya.
        const lompat = (kiri) => {
            const semula = trek.style.scrollBehavior;
            trek.style.scrollBehavior = 'auto';
            trek.scrollLeft = kiri;
            trek.style.scrollBehavior = semula;
        };

        const geserHalus = (kiri) => {
            trek.scrollTo({ left: kiri, behavior: kurangiGerak.matches ? 'auto' : 'smooth' });
        };

        // ---------- Mode tanpa ujung (data-geser-putar) ----------
        // Urutan trek: [salinan item terakhir] item 1 … item n [salinan item pertama].
        // "Indeks luas" e: 0 = salinan awal, 1..n = item asli, n + 1 = salinan akhir.
        const n = daftarItem.length;
        const tanpaUjung = putar && n > 1;
        let salinanAwal = null;
        let salinanAkhir = null;
        let tujuanLuas = null; // indeks luas yang sedang dituju animasi geser

        if (tanpaUjung) {
            const salin = (item) => {
                const salinan = item.cloneNode(true);
                salinan.removeAttribute('data-geser-item');
                salinan.setAttribute('aria-hidden', 'true');
                salinan.inert = true;
                salinan.querySelectorAll('[id]').forEach((elemen) => elemen.removeAttribute('id'));
                salinan.querySelectorAll('img').forEach((gambar) => gambar.removeAttribute('fetchpriority'));
                return salinan;
            };
            salinanAwal = salin(daftarItem[n - 1]);
            salinanAkhir = salin(daftarItem[0]);
            trek.prepend(salinanAwal);
            trek.append(salinanAkhir);
            // Dalam tugas yang sama dengan penyisipan, sehingga tidak ada bingkai yang menampilkan salinan.
            lompat(posisiItem(daftarItem[0]));
        }

        const elemenLuas = (e) => {
            if (e <= 0) return salinanAwal;
            if (e >= n + 1) return salinanAkhir;
            return daftarItem[e - 1];
        };

        // Jika posisi sudah melewati item asli (berada di daerah salinan), pindahkan seketika
        // sejauh n item ke daerah asli yang tampilannya sama persis. Animasi yang sedang
        // berjalan ikut dipindahkan, sehingga geseran berikutnya tetap searah.
        const normalkan = () => {
            const lebar = langkah();
            const e = trek.scrollLeft / lebar;
            let pindah = 0;

            if (e > n + 0.5) {
                pindah = -n;
            } else if (e < 0.5) {
                pindah = n;
            }

            if (pindah !== 0) {
                lompat(trek.scrollLeft + pindah * lebar);
                if (tujuanLuas !== null) {
                    tujuanLuas += pindah;
                }
            }

            return e + pindah;
        };

        // Setelah berhenti di salinan, pindah ke item aslinya.
        const rapikan = () => {
            const e = Math.round(trek.scrollLeft / langkah());
            if (e >= n + 1) {
                lompat(posisiItem(daftarItem[0]));
            } else if (e <= 0) {
                lompat(posisiItem(daftarItem[n - 1]));
            }
        };

        // Geser satu item ke kanan (+1) atau ke kiri (-1), selalu searah tombol yang ditekan.
        const geserArah = (arah) => {
            if (jumlahHalaman < 2) {
                return;
            }

            if (!tanpaUjung) {
                keHalaman(halaman + arah);
                return;
            }

            const e = normalkan();
            let tujuan = (tujuanLuas ?? Math.round(e)) + arah;

            // Tombol ditekan lebih cepat dari animasi sampai tujuan melewati salinan di ujung.
            if (tujuan > n + 1 || tujuan < 0) {
                const pindah = tujuan > n + 1 ? -n : n;
                if (e + pindah >= 0 && e + pindah <= n + 1) {
                    // Pindahkan posisi sekarang ke daerah yang tampilannya sama, lalu lanjut searah.
                    lompat(trek.scrollLeft + pindah * langkah());
                    tujuan += pindah;
                } else {
                    // Terlalu jauh di depan animasi: selesaikan geseran sebelumnya seketika, lalu lanjutkan.
                    const dasar = tujuanLuas >= n + 1 ? 1 : (tujuanLuas <= 0 ? n : tujuanLuas);
                    lompat(posisiItem(elemenLuas(dasar)));
                    tujuan = dasar + arah;
                }
            }

            tujuanLuas = tujuan;
            geserHalus(posisiItem(elemenLuas(tujuanLuas)));
        };

        const hitung = () => {
            // Dibulatkan karena lebar trek juga memuat padding dan celah antar-item.
            perHalaman = Math.max(1, Math.round(trek.clientWidth / langkah()));
            jumlahHalaman = trek.scrollWidth - trek.clientWidth > 4
                ? Math.ceil(daftarItem.length / perHalaman)
                : 1;
        };

        const halamanSekarang = () => {
            if (tanpaUjung) {
                const e = Math.round(trek.scrollLeft / langkah());
                return (((e - 1) % n) + n) % n; // salinan dihitung sebagai item aslinya
            }
            if (trek.scrollLeft + trek.clientWidth >= trek.scrollWidth - 4) {
                return jumlahHalaman - 1;
            }
            return Math.min(jumlahHalaman - 1, Math.round(trek.scrollLeft / (langkah() * perHalaman)));
        };

        // Item yang sama sekali tidak terlihat tidak dapat difokus atau diklik.
        const aturInert = () => {
            const kiri = trek.scrollLeft;
            const kanan = kiri + trek.clientWidth;
            daftarItem.forEach((item) => {
                const awal = posisiItem(item);
                item.inert = awal + item.offsetWidth <= kiri + 1 || awal >= kanan - 1;
            });
        };

        const perbarui = () => {
            halaman = halamanSekarang();

            if (wadahTitik) {
                [...wadahTitik.children].forEach((titik, i) => {
                    titik.setAttribute('aria-current', i === halaman ? 'true' : 'false');
                });
            }

            if (!putar) {
                if (tombolSebelum) tombolSebelum.disabled = halaman === 0;
                if (tombolBerikut) tombolBerikut.disabled = halaman === jumlahHalaman - 1;
            }

            aturInert();
        };

        const keHalaman = (tujuan) => {
            if (jumlahHalaman < 2) {
                return;
            }

            if (tanpaUjung) {
                // Titik halaman: langsung ke item asli yang dipilih. Dari item terakhir ke item pertama
                // tetap bergeser maju satu langkah lewat sambungan ujung (seperti tombol panah), tidak
                // mundur melewati semua item; begitu juga dari item pertama ke item terakhir.
                const e = normalkan();
                const dari = tujuanLuas ?? Math.round(e); // indeks luas yang ditempati/sedang dituju
                const dariAsli = (((dari - 1) % n) + n) % n + 1;
                const sasaran = Math.min(Math.max(tujuan, 0), n - 1) + 1;

                if (sasaran === dariAsli) {
                    tujuanLuas = dari;
                } else if (dariAsli === n && sasaran === 1) {
                    tujuanLuas = dari + 1;
                } else if (n > 2 && dariAsli === 1 && sasaran === n) {
                    tujuanLuas = dari - 1;
                } else {
                    tujuanLuas = sasaran;
                }
                geserHalus(posisiItem(elemenLuas(tujuanLuas)));
                return;
            }

            let nomor = tujuan;
            if (putar) {
                nomor = (tujuan + jumlahHalaman) % jumlahHalaman;
            } else {
                nomor = Math.min(Math.max(tujuan, 0), jumlahHalaman - 1);
            }

            const item = daftarItem[Math.min(nomor * perHalaman, daftarItem.length - 1)];
            trek.scrollTo({ left: posisiItem(item), behavior: kurangiGerak.matches ? 'auto' : 'smooth' });
        };

        const buatTitik = () => {
            if (!wadahTitik) {
                return;
            }

            wadahTitik.textContent = '';
            for (let i = 0; i < jumlahHalaman; i += 1) {
                const titik = document.createElement('button');
                titik.type = 'button';
                titik.className = 'titik';
                titik.setAttribute('aria-label', `${nama} ${i + 1} dari ${jumlahHalaman}`);
                titik.addEventListener('click', () => {
                    keHalaman(i);
                    aturOtomatis();
                });
                wadahTitik.append(titik);
            }
        };

        const tampilkanKendali = () => {
            const perlu = jumlahHalaman > 1;
            [tombolSebelum, tombolBerikut, wadahTitik, wadahKendali].forEach((elemen) => {
                if (elemen) elemen.hidden = !perlu;
            });
        };

        // ---------- Pergantian otomatis ----------
        // Menu HP (tombol lambang) terbuka: banner di belakangnya ikut diam, berganti lagi setelah menu ditutup.
        const menuTerbuka = () => document.documentElement.classList.contains('menu-dial-terbuka');
        const bolehOtomatis = () => detikOtomatis > 0 && !dijeda && !ditahan && !disorot && !document.hidden && !menuTerbuka() && jumlahHalaman > 1;

        const aturOtomatis = () => {
            window.clearInterval(pewaktu);
            pewaktu = null;

            if (bolehOtomatis()) {
                pewaktu = window.setInterval(() => geserArah(1), detikOtomatis * 1000);
            }

            // Saat berganti otomatis, pembaca layar tidak diganggu pengumuman.
            trek.setAttribute('aria-live', pewaktu ? 'off' : 'polite');
        };

        const perbaruiTombolJeda = () => {
            if (!tombolJeda) {
                return;
            }
            tombolJeda.classList.toggle('sedang-jeda', dijeda);
            tombolJeda.setAttribute('aria-label', dijeda ? 'Putar pergantian otomatis' : 'Jeda pergantian otomatis');
        };

        // ---------- Peristiwa ----------
        tombolSebelum?.addEventListener('click', () => {
            geserArah(-1);
            aturOtomatis();
        });
        tombolBerikut?.addEventListener('click', () => {
            geserArah(1);
            aturOtomatis();
        });
        tombolJeda?.addEventListener('click', () => {
            dijeda = !dijeda;
            perbaruiTombolJeda();
            aturOtomatis();
        });

        akar.addEventListener('keydown', (event) => {
            if (event.target.closest('input, textarea, select')) {
                return;
            }
            if (event.key === 'ArrowLeft') {
                geserArah(-1);
                aturOtomatis();
            } else if (event.key === 'ArrowRight') {
                geserArah(1);
                aturOtomatis();
            }
        });

        akar.addEventListener('focusin', () => { ditahan = true; aturOtomatis(); });
        akar.addEventListener('focusout', (event) => {
            if (!akar.contains(event.relatedTarget)) {
                ditahan = false;
                aturOtomatis();
            }
        });
        // Kursor mouse di atas carousel: berhenti sementara agar tulisan sempat dibaca. Sentuhan jari
        // tidak dihitung, karena di layar sentuh tidak ada keadaan "kursor pergi".
        akar.addEventListener('pointerenter', (event) => {
            if (event.pointerType === 'mouse') {
                disorot = true;
                aturOtomatis();
            }
        });
        akar.addEventListener('pointerleave', (event) => {
            if (event.pointerType === 'mouse') {
                disorot = false;
                aturOtomatis();
            }
        });
        document.addEventListener('visibilitychange', aturOtomatis);
        document.addEventListener('menu-dial', aturOtomatis);
        // Pengunjung menyalakan "kurangi animasi" saat halaman terbuka: pergantian otomatis dihentikan.
        kurangiGerak.addEventListener('change', () => {
            if (kurangiGerak.matches) {
                dijeda = true;
                perbaruiTombolJeda();
                aturOtomatis();
            }
        });

        // Setelah geseran (tombol, otomatis, atau jari) benar-benar berhenti.
        const selesaiGulir = () => {
            // Selama kursor masih menyeret, posisi belum final: jangan dirapikan.
            if (sedangDiseret) {
                return;
            }
            // Browser juga mengirim "scrollend" saat animasi diputus klik berikutnya. Selama
            // tujuan belum tercapai, animasi masih berjalan: jangan dirapikan dulu.
            if (tujuanLuas !== null && Math.abs(trek.scrollLeft - posisiItem(elemenLuas(tujuanLuas))) > 2) {
                return;
            }
            tujuanLuas = null;
            if (tanpaUjung) {
                rapikan();
            }
            trek.classList.remove('sedang-diseret'); // scroll-snap aktif lagi setelah seretan kursor selesai
            perbarui();
        };

        // Geseran dengan jari, trackpad, atau roda tetikus menggantikan tujuan animasi tombol,
        // dan jeda otomatis dihitung ulang agar item pilihan pengunjung tampil satu jeda penuh.
        ['pointerdown', 'wheel', 'touchstart'].forEach((jenis) => {
            trek.addEventListener(jenis, () => {
                tujuanLuas = null;
                aturOtomatis();
            }, { passive: true });
        });

        // ---------- Seret dengan kursor mouse (data-geser-seret) ----------
        // Jari dan trackpad sudah dapat menggeser trek secara bawaan (scroll-snap), mouse tidak.
        // Saat diseret, trek mengikuti kursor; saat dilepas bergeser ke item berikutnya atau
        // sebelumnya (tanpa ujung, sama seperti tombol panah), atau kembali ke item semula bila
        // seretannya pendek. Klik pada tautan di dalam slide sesudah menyeret dibatalkan.
        if (akar.hasAttribute('data-geser-seret')) {
            let seret = null;
            let abaikanKlik = false;

            // Gambar dan tautan tidak ikut terseret sebagai "bayangan" bawaan browser.
            trek.addEventListener('dragstart', (event) => event.preventDefault());

            trek.addEventListener('pointerdown', (event) => {
                if (event.pointerType === 'touch' || event.button !== 0 || jumlahHalaman < 2) {
                    return;
                }
                if (tanpaUjung) {
                    rapikan();
                }
                seret = { id: event.pointerId, x: event.clientX, kiri: trek.scrollLeft, bergerak: false };
            });

            trek.addEventListener('pointermove', (event) => {
                if (!seret || event.pointerId !== seret.id) {
                    return;
                }
                const jarak = event.clientX - seret.x;
                if (!seret.bergerak) {
                    if (Math.abs(jarak) < 6) {
                        return; // gerakan kecil masih dianggap klik
                    }
                    seret.bergerak = true;
                    sedangDiseret = true;
                    trek.setPointerCapture(event.pointerId);
                    trek.classList.add('sedang-diseret');
                }
                trek.scrollLeft = seret.kiri - jarak;
            });

            const lepas = (event) => {
                if (!seret || event.pointerId !== seret.id) {
                    return;
                }
                const { bergerak, x, kiri } = seret;
                seret = null;
                if (!bergerak) {
                    return;
                }
                sedangDiseret = false;
                abaikanKlik = true;
                window.setTimeout(() => { abaikanKlik = false; }, 0);

                // Seretan dibatalkan browser: kembali ke item semula.
                const jarak = event.type === 'pointercancel' ? 0 : event.clientX - x;
                const arah = Math.abs(jarak) >= Math.min(80, trek.clientWidth * 0.15) ? (jarak < 0 ? 1 : -1) : 0;
                const lebar = langkah();

                if (tanpaUjung) {
                    tujuanLuas = Math.min(Math.max(Math.round(kiri / lebar) + arah, 0), n + 1);
                    const kiriTujuan = posisiItem(elemenLuas(tujuanLuas));
                    if (Math.abs(trek.scrollLeft - kiriTujuan) <= 2) {
                        selesaiGulir(); // sudah tepat di tujuan, tidak ada geseran lagi
                    } else {
                        geserHalus(kiriTujuan);
                    }
                } else {
                    keHalaman(Math.round(kiri / (lebar * perHalaman)) + arah);
                }
                aturOtomatis();
            };
            trek.addEventListener('pointerup', lepas);
            trek.addEventListener('pointercancel', lepas);
            trek.addEventListener('click', (event) => {
                if (abaikanKlik) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
        }

        const adaScrollend = 'onscrollend' in window;
        if (adaScrollend) {
            trek.addEventListener('scrollend', selesaiGulir);
        }

        let tundaGulir = null;
        trek.addEventListener('scroll', () => {
            window.clearTimeout(tundaGulir);
            // Browser lama tanpa peristiwa scrollend: dianggap berhenti setelah 150 ms tanpa gerakan.
            tundaGulir = window.setTimeout(adaScrollend ? perbarui : selesaiGulir, adaScrollend ? 80 : 150);
        }, { passive: true });

        const susunUlang = () => {
            if (tanpaUjung) {
                // Lebar berubah (misalnya layar diputar): tetap di item yang sama.
                lompat(posisiItem(daftarItem[halaman]));
            }
            hitung();
            buatTitik();
            tampilkanKendali();
            perbarui();
            aturOtomatis();
        };

        if ('ResizeObserver' in window) {
            new ResizeObserver(susunUlang).observe(trek);
        } else {
            window.addEventListener('resize', susunUlang);
        }

        perbaruiTombolJeda();
        susunUlang();
    };

    const mulai = () => document.querySelectorAll('[data-geser]').forEach(pasang);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulai);
    } else {
        mulai();
    }
})();
