/*
 * Interaksi kecil di sisi browser. Website tetap dapat dipakai tanpa
 * JavaScript; skrip ini hanya menambah kenyamanan.
 */
document.documentElement.classList.add('js');

// Layar sentuh: Safari di iPhone/iPad baru menyalakan :active (misalnya warna menu navbar saat ditekan)
// bila halaman punya pendengar touchstart. Pendengar kosong ini pasif, jadi tidak memperlambat gulir.
document.addEventListener('touchstart', () => {}, { passive: true });

document.addEventListener('DOMContentLoaded', () => {
    // Menu ponsel (publik, akun, dan admin): buka/tutup dengan tombol,
    // tutup dengan tombol Esc.
    document.querySelectorAll('[data-kepala]').forEach((kepala) => {
        const tombolMenu = kepala.querySelector('[data-tombol-menu]');

        if (!tombolMenu) {
            return;
        }

        // Menu publik di HP berupa "speed dial" layar penuh (kepala__menu--dial): mengetuk area kosong di sekitar
        // menu menutupnya. Gulir halaman di belakangnya ditahan oleh CSS lapisan menu (overscroll-behavior), dan
        // selama terbuka banner berhenti berganti sendiri (peristiwa "menu-dial" didengar geser.js).
        const menu = document.getElementById(tombolMenu.getAttribute('aria-controls'));
        const dial = !!menu && menu.classList.contains('kepala__menu--dial');

        const atur = (terbuka) => {
            kepala.dataset.terbuka = String(terbuka);
            tombolMenu.setAttribute('aria-expanded', String(terbuka));
            if (dial) {
                document.documentElement.classList.toggle('menu-dial-terbuka', terbuka);
                document.dispatchEvent(new CustomEvent('menu-dial', { detail: { terbuka } }));
            }
        };

        tombolMenu.addEventListener('click', () => {
            atur(tombolMenu.getAttribute('aria-expanded') !== 'true');
        });

        if (dial) {
            menu.addEventListener('click', (event) => {
                if (event.target === menu) {
                    atur(false);
                }
            });
            // Kembali lewat tombol Back (bfcache): halaman dipulihkan dengan menu tertutup.
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    atur(false);
                }
            });
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && tombolMenu.getAttribute('aria-expanded') === 'true') {
                atur(false);
                tombolMenu.focus();
            }
        });
    });

    // Dropdown menu navbar (Profil: Profil Perusahaan, Kontak, Kemitraan).
    // - Klik/ketuk atau Enter/Spasi membuka dan menutup.
    // - Di layar lebar dengan mouse juga terbuka saat diarahkan, dan tertutup saat ditinggalkan.
    // - Esc, klik di luar, atau pindah fokus keluar menutupnya.
    const layarLebarMouse = window.matchMedia('(min-width: 1024px) and (hover: hover) and (pointer: fine)');
    document.querySelectorAll('[data-submenu]').forEach((grup) => {
        const pemicu = grup.querySelector('.menu__pemicu');

        if (!pemicu) {
            return;
        }

        let jedaTutup = 0;
        let dibukaHover = false;
        const terbuka = () => pemicu.getAttribute('aria-expanded') === 'true';
        const atur = (buka) => {
            window.clearTimeout(jedaTutup);
            pemicu.setAttribute('aria-expanded', String(buka));
            if (!buka) {
                dibukaHover = false;
            }
        };

        pemicu.addEventListener('click', () => {
            // Klik pertama setelah menu terbuka karena mouse: tetap terbuka (tidak langsung tertutup).
            if (dibukaHover) {
                dibukaHover = false;
                return;
            }
            atur(!terbuka());
        });

        grup.addEventListener('pointerenter', (event) => {
            if (event.pointerType !== 'mouse' || !layarLebarMouse.matches) {
                return;
            }
            window.clearTimeout(jedaTutup);
            if (!terbuka()) {
                atur(true);
                dibukaHover = true;
            }
        });

        grup.addEventListener('pointerleave', (event) => {
            if (event.pointerType === 'mouse' && layarLebarMouse.matches) {
                jedaTutup = window.setTimeout(() => atur(false), 250);
            }
        });

        grup.addEventListener('focusout', (event) => {
            if (!grup.contains(event.relatedTarget)) {
                atur(false);
            }
        });

        document.addEventListener('click', (event) => {
            if (terbuka() && !grup.contains(event.target)) {
                atur(false);
            }
        });

        grup.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && terbuka()) {
                event.stopPropagation(); // menu ponsel tetap terbuka, hanya submenu yang ditutup
                atur(false);
                pemicu.focus();
            }
        });
    });

    // Navbar transparan di atas banner beranda: menjadi putih (padat) setelah halaman digulir, atau saat menu
    // ponsel model panel dibuka. Menu "speed dial" (tombol lambang) melayang di atas halaman, jadi navbar tetap
    // seperti sebelum menu dibuka (revisi: dulu ikut berubah putih).
    document.querySelectorAll('.kepala--transparan').forEach((kepala) => {
        const menuDial = kepala.querySelector('.kepala__menu--dial');
        const atur = () => {
            const padat = window.scrollY > 8 || (!menuDial && kepala.dataset.terbuka === 'true');
            kepala.classList.toggle('kepala--padat', padat);
        };
        window.addEventListener('scroll', atur, { passive: true });
        new MutationObserver(atur).observe(kepala, { attributes: true, attributeFilter: ['data-terbuka'] });
        atur();
    });

    // Tombol mata untuk melihat/menyembunyikan kata sandi.
    document.querySelectorAll('[data-lihat-sandi]').forEach((tombol) => {
        const kolom = document.getElementById(tombol.getAttribute('aria-controls'));

        if (!kolom) {
            return;
        }

        tombol.hidden = false;
        tombol.addEventListener('click', () => {
            const tampil = kolom.type === 'password';
            kolom.type = tampil ? 'text' : 'password';
            tombol.setAttribute('aria-pressed', String(tampil));
            const label = tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
            tombol.setAttribute('aria-label', label);
            tombol.title = label;
        });
    });

    // Sebelum form dikirim, kolom sandi dikembalikan tersembunyi agar browser
    // tidak menyimpan isinya sebagai teks biasa (misalnya di riwayat isian).
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-lihat-sandi][aria-pressed="true"]').forEach((tombol) => tombol.click());
        });
    });

    // Video hiasan (animasi logo di Profil): jika pengunjung memilih
    // "kurangi animasi", video dihentikan dan gambar diamnya (poster) ditampilkan.
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');
    document.querySelectorAll('video[data-animasi-hiasan]').forEach((video) => {
        const atur = () => {
            if (kurangiGerak.matches) {
                video.autoplay = false;
                video.pause();
                video.load(); // kembali menampilkan poster
            } else {
                video.play().catch(() => {});
            }
        };
        kurangiGerak.addEventListener?.('change', atur);
        if (kurangiGerak.matches) {
            atur();
        }
    });

    // Kartu paket 3D (beranda dan halaman Paket). Hanya mengubah transform dan opacity,
    // sehingga dikerjakan GPU perangkat pengunjung, bukan server.
    // - Kartu yang belum terlihat muncul dengan efek berdiri dan naik saat digulir ke layar.
    // - Dengan mouse, kartu miring mengikuti kursor dan isinya tampak timbul berlapis.
    // Dilewati jika pengunjung memilih "kurangi animasi"; tanpa JavaScript kartu tetap datar.
    const daftarKartu = document.querySelectorAll('.kartu-paket');
    if (daftarKartu.length > 0 && !kurangiGerak.matches) {
        const MIRING_MAKS = 6; // derajat, saat kursor di tepi kartu
        const pakaiMouse = window.matchMedia('(hover: hover) and (pointer: fine)');
        const pengamat = 'IntersectionObserver' in window
            ? new IntersectionObserver((daftarEntri) => {
                daftarEntri
                    .filter((entri) => entri.isIntersecting)
                    .forEach((entri, urutan) => {
                        pengamat.unobserve(entri.target);
                        entri.target.style.setProperty('--urutan', urutan);
                        entri.target.classList.remove('kartu-3d--tunggu');
                    });
            }, { threshold: 0.15 })
            : null;

        daftarKartu.forEach((kartu) => {
            const pembungkus = kartu.parentElement;
            pembungkus.classList.add('kartu-3d');
            pembungkus.parentElement.classList.add('kartu-3d-wadah');

            // Hanya kartu yang masih di bawah layar yang dianimasikan, agar kartu yang
            // sudah tampil saat halaman dibuka tidak berkedip.
            if (pengamat && pembungkus.getBoundingClientRect().top > window.innerHeight) {
                pembungkus.classList.add('kartu-3d--tunggu');
                pengamat.observe(pembungkus);
            }

            // Posisi kursor dibaca dari pembungkus (tidak ikut miring) agar gerakan stabil,
            // lalu diterapkan sekali per frame layar.
            let bingkai = 0;
            let posisi = null;

            const terapkan = () => {
                bingkai = 0;
                if (!posisi) {
                    return;
                }
                kartu.style.setProperty('--putar-x', `${((posisi.y - 0.5) * 2 * MIRING_MAKS).toFixed(2)}deg`);
                kartu.style.setProperty('--putar-y', `${((0.5 - posisi.x) * 2 * MIRING_MAKS).toFixed(2)}deg`);
                kartu.style.setProperty('--kilau-x', `${(posisi.x * 100).toFixed(1)}%`);
                kartu.style.setProperty('--kilau-y', `${(posisi.y * 100).toFixed(1)}%`);
            };

            pembungkus.addEventListener('pointermove', (event) => {
                if (event.pointerType !== 'mouse' || !pakaiMouse.matches || kurangiGerak.matches) {
                    return;
                }
                const kotak = pembungkus.getBoundingClientRect();
                posisi = {
                    x: Math.min(Math.max((event.clientX - kotak.left) / kotak.width, 0), 1),
                    y: Math.min(Math.max((event.clientY - kotak.top) / kotak.height, 0), 1),
                };
                kartu.classList.add('kartu-paket--miring');
                if (!bingkai) {
                    bingkai = requestAnimationFrame(terapkan);
                }
            });

            pembungkus.addEventListener('pointerleave', () => {
                posisi = null;
                cancelAnimationFrame(bingkai);
                bingkai = 0;
                kartu.classList.remove('kartu-paket--miring');
            });
        });
    }

    // Cegah form terkirim dua kali: tombol kirim dinonaktifkan setelah diklik.
    document.querySelectorAll('form[data-cegah-ganda]').forEach((form) => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('button[type="submit"]').forEach((tombol) => {
                tombol.disabled = true;
                if (tombol.dataset.teksProses) {
                    tombol.dataset.teksAwal = tombol.textContent;
                    tombol.textContent = tombol.dataset.teksProses;
                }
            });
        });
    });
});

// Saat pengguna menekan "Kembali", browser dapat memulihkan halaman dari cache
// dengan tombol masih nonaktif. Aktifkan kembali agar form tetap bisa dipakai.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('form[data-cegah-ganda] button[type="submit"]').forEach((tombol) => {
        tombol.disabled = false;
        if (tombol.dataset.teksAwal) {
            tombol.textContent = tombol.dataset.teksAwal;
        }
    });
});
