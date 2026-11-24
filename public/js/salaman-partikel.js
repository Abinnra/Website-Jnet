/*
 * Banner Kemitraan (revisi mitra): animasi "jabat tangan digital" bergaya motion graphic,
 * senada dengan video logo di banner Profil (latar terang, garis serat optik, warna JNET).
 *
 * Bentuk tangan berupa gambar garis (seperti ikon jabat tangan): manset miring di kiri-atas dan
 * kanan-atas, ibu jari tangan kanan menindih tangan kiri, garis diagonal dari pangkal ibu jari,
 * ujung jari bertingkat di kanan bawah, dan empat jari melingkar di kiri bawah.
 *
 * Satu siklus (14 detik, berulang seperti GIF):
 * 1. Titik-titik cahaya terbang dari kiri (biru) dan kanan (oranye), lalu mendarat di sepanjang
 *    garis tangan sehingga garis tepinya tergambar sedikit demi sedikit.
 * 2. Tangan bersalaman (naik-turun) beberapa kali, diikuti gelombang cahaya dari genggaman.
 * 3. Titik menyebar dan memudar, lalu siklus dimulai lagi.
 *
 * Animasi hanya berjalan saat banner terlihat di layar. Jika pengunjung memilih
 * "kurangi animasi", yang tampil adalah gambar diam tangan yang sudah terbentuk.
 */
(() => {
    const kanvas = document.querySelector('[data-salaman-partikel]');

    if (!kanvas || !kanvas.getContext) {
        return;
    }

    const ctx = kanvas.getContext('2d');
    const banner = kanvas.closest('[data-salaman-banner]') || kanvas.parentElement;
    const teks = banner.querySelector('[data-salaman-teks]');
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

    // ---------- Waktu (detik dalam satu siklus) ----------
    const SIKLUS = 14;
    const LAMA_TERBANG = 1.3;
    const SALAMAN = [3.6, 7.2, 10.5]; // tangan bersalaman + gelombang cahaya
    const MULAI_MENYEBAR = 12.3;

    // ---------- Gambar garis jabat tangan (satuan 420 x 300) ----------
    const GENGGAMAN = { x: 210, y: 155 };
    const MANSET_KIRI = [30, 100, 110]; // putaran: derajat, pusat x, pusat y
    const MANSET_KANAN = [-30, 320, 110];
    const WARNA = {
        biru: ['#0077C5', '#1A8FD8', '#00ADEF'],
        oranye: ['#FF6501', '#FF8A3D', '#F7931E'],
        tangan: ['#021D3A', '#0A3A73', '#123F7A'],
        manset: ['#003399', '#0A3A73'],
    };
    const WARNA_GARIS = { biru: '0, 119, 197', oranye: '255, 101, 1', tangan: '2, 29, 58', manset: '0, 51, 153' };

    const kotakBulat = (x, y, w, h, r) => `M${x + r} ${y} H${x + w - r} A${r} ${r} 0 0 1 ${x + w} ${y + r} V${y + h - r} `
        + `A${r} ${r} 0 0 1 ${x + w - r} ${y + h} H${x + r} A${r} ${r} 0 0 1 ${x} ${y + h - r} V${y + r} A${r} ${r} 0 0 1 ${x + r} ${y} Z`;

    // jenis = warna; mulai = jeda (detik) sebelum titik garis ini mulai terbang.
    const GARIS_TANGAN = [
        // lengan baju dari kiri-atas (biru) dan kanan-atas (oranye), memudar ke tepi banner
        { jenis: 'biru', mulai: 0, putar: MANSET_KIRI, d: 'M-60 64 L85 64' },
        { jenis: 'biru', mulai: 0, putar: MANSET_KIRI, d: 'M-60 156 L85 156' },
        { jenis: 'oranye', mulai: 0, putar: MANSET_KANAN, d: 'M480 64 L335 64' },
        { jenis: 'oranye', mulai: 0, putar: MANSET_KANAN, d: 'M480 156 L335 156' },
        // manset kemeja
        { jenis: 'manset', mulai: 0.35, putar: MANSET_KIRI, d: kotakBulat(85, 58, 30, 104, 6) },
        { jenis: 'manset', mulai: 0.35, putar: MANSET_KANAN, d: kotakBulat(305, 58, 30, 104, 6) },
        // pergelangan tangan kiri: tepi atas dan tepi bawah
        { jenis: 'tangan', mulai: 0.7, d: 'M135 81 C147 90 158 93 171 93' },
        { jenis: 'tangan', mulai: 0.7, d: 'M95 149 C101 156 106 163 112 168' },
        // tangan kanan: punggung tangan, ibu jari, lalu garis diagonal dari pangkal ibu jari
        { jenis: 'tangan', mulai: 0.7, d: 'M287 83 C270 88 250 84 230 80 C212 77 196 78 184 82 C178 84 174 88 171 93 C166 100 160 110 156 120 C152 128 152 135 158 137 C166 140 178 136 188 129 C194 124 198 118 202 113 C204 110 206 108 209 107 C222 112 240 122 258 133 C275 144 290 156 303 170' },
        // ujung jari bertingkat di kanan bawah dan tepi bawah genggaman
        { jenis: 'tangan', mulai: 0.85, d: 'M333 162 C324 166 312 168 303 170 C306 176 302 186 292 187 C294 196 286 205 276 205 C277 214 268 221 259 220 C258 230 246 236 232 237 C220 238 210 235 202 232' },
        // garis antar-jari
        { jenis: 'tangan', mulai: 1, d: 'M235 163 L292 187' },
        { jenis: 'tangan', mulai: 1, d: 'M221 185 L276 205' },
        { jenis: 'tangan', mulai: 1, d: 'M210 209 L259 220' },
        // empat jari yang melingkar di kiri bawah (miring "/")
        ...[[124, 181, 34], [147, 190, 56], [167, 208, 50], [187, 224, 42]].map(([cx, cy, h]) => ({
            jenis: 'tangan', mulai: 0.85, putar: [40, cx, cy], d: kotakBulat(cx - 11, cy - h / 2, 22, h, 11),
        })),
    ];

    const acak = (min, maks) => min + Math.random() * (maks - min);
    const pilih = (daftar) => daftar[Math.floor(Math.random() * daftar.length)];
    const batas = (n) => Math.min(1, Math.max(0, n));
    const mudahKeluar = (p) => 1 - (1 - p) ** 3;

    const putarTitik = ([x, y], [derajat, cx, cy]) => {
        const sudut = (derajat * Math.PI) / 180;
        const dx = x - cx;
        const dy = y - cy;
        return [cx + dx * Math.cos(sudut) - dy * Math.sin(sudut), cy + dx * Math.sin(sudut) + dy * Math.cos(sudut)];
    };

    // ---------- Ubah garis menjadi titik berjarak sama (memakai pengukur jalur SVG bawaan browser) ----------
    const NS = 'http://www.w3.org/2000/svg';
    const pengukur = document.createElementNS(NS, 'svg');
    pengukur.setAttribute('width', '0');
    pengukur.setAttribute('height', '0');
    pengukur.style.position = 'absolute';
    pengukur.style.visibility = 'hidden';
    const jalurUkur = document.createElementNS(NS, 'path');
    pengukur.appendChild(jalurUkur);
    document.body.appendChild(pengukur);

    const hp = window.innerWidth < 600;
    const JARAK_TITIK = hp ? 4.2 : 3.2;
    const titik = [];
    const sambungan = []; // pasangan titik berurutan pada garis yang sama

    GARIS_TANGAN.forEach((garis) => {
        jalurUkur.setAttribute('d', garis.d);
        const panjang = jalurUkur.getTotalLength();
        const tertutup = /z\s*$/i.test(garis.d);
        const jumlah = Math.max(2, Math.round(panjang / JARAK_TITIK));
        const pembagi = tertutup ? jumlah : jumlah - 1;
        const awal = titik.length;

        for (let i = 0; i < jumlah; i += 1) {
            const p = jalurUkur.getPointAtLength((panjang * i) / pembagi);
            const [tx, ty] = garis.putar ? putarTitik([p.x, p.y], garis.putar) : [p.x, p.y];
            titik.push({
                tx,
                ty,
                jenis: garis.jenis,
                warna: pilih(WARNA[garis.jenis]),
                // titik terbang dari sisi terdekat: kiri (biru) atau kanan (oranye)
                sisi: tx < GENGGAMAN.x ? -1 : 1,
                r: acak(1.1, 1.8),
                fase: Math.random() * Math.PI * 2,
                halo: Math.random() < 0.09,
                // garis tergambar berurutan dari awal ke akhir jalurnya
                tunda: garis.mulai + (i / jumlah) * 0.9 + Math.random() * 0.2,
            });
            if (i > 0) {
                sambungan.push([titik.length - 2, titik.length - 1]);
            }
        }
        if (tertutup) {
            sambungan.push([titik.length - 1, awal]);
        }
    });
    pengukur.remove();

    const jarakGenggaman = (t) => Math.hypot(t.tx - GENGGAMAN.x, (t.ty - GENGGAMAN.y) * 1.2);
    titik.forEach((t) => {
        // Ujung lengan memudar, sehingga lengan seolah datang dari luar banner.
        const jarak = jarakGenggaman(t);
        t.pudar = jarak < 150 ? 1 : Math.max(0, 1 - (jarak - 150) / 120);
        // Seberapa jauh titik ikut bergoyang saat bersalaman (ujung lengan hampir diam).
        t.goyang = Math.max(0, 1 - jarak / 240);
    });

    // Gambar cahaya lembut (halo) per warna, dibuat sekali lalu dipakai ulang.
    const halo = new Map();
    const ambilHalo = (warna) => {
        if (!halo.has(warna)) {
            const c = document.createElement('canvas');
            c.width = 32;
            c.height = 32;
            const g = c.getContext('2d');
            const gradasi = g.createRadialGradient(16, 16, 0, 16, 16, 16);
            gradasi.addColorStop(0, warna);
            gradasi.addColorStop(1, 'rgba(255, 255, 255, 0)');
            g.fillStyle = gradasi;
            g.fillRect(0, 0, 32, 32);
            halo.set(warna, c);
        }
        return halo.get(warna);
    };

    // ---------- Tata letak ----------
    let lebar = 0;
    let tinggi = 0;
    let skala = 1;
    let pusat = { x: 0, y: 0 };
    let serat = [];
    let batasTeks = -Infinity; // tepi kanan teks (layar lebar): titik di belakang teks dipudarkan

    const aturUkuran = () => {
        const kotak = banner.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        lebar = kotak.width;
        tinggi = kotak.height;
        kanvas.width = Math.round(lebar * dpr);
        kanvas.height = Math.round(tinggi * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const kotakTeks = teks ? teks.getBoundingClientRect() : null;
        let area;
        if (lebar >= 900 && kotakTeks) {
            // Layar lebar: tangan di kanan teks.
            const kiri = kotakTeks.right - kotak.left + 24;
            const atas = parseFloat(getComputedStyle(banner).paddingTop) || 0;
            area = { x: kiri, y: atas, w: lebar - kiri - 16, h: tinggi - atas };
            batasTeks = kotakTeks.right - kotak.left;
        } else {
            // HP/tablet: tangan di bawah teks.
            const atas = kotakTeks ? kotakTeks.bottom - kotak.top + 4 : tinggi * 0.45;
            area = { x: 0, y: atas, w: lebar, h: Math.max(150, tinggi - atas) };
            batasTeks = -Infinity;
        }
        skala = Math.min(area.w / 330, area.h / 215);
        pusat = { x: area.x + area.w / 2, y: area.y + area.h / 2 };

        // Serat optik di latar: melengkung dari kiri ke kanan, melewati sekitar genggaman.
        serat = Array.from({ length: hp ? 5 : 8 }, (_, i) => {
            const y0 = acak(0.1, 0.9) * tinggi;
            const y3 = acak(0.1, 0.9) * tinggi;
            const lewat = pusat.y + acak(-110, 110) * skala;
            return {
                titik: [[-60, y0], [lebar * 0.32, y0 + acak(-80, 80)], [lebar * 0.62, lewat], [lebar + 60, y3]],
                warna: i % 2 === 0 ? '0, 119, 197' : '255, 101, 1',
                laju: acak(0.07, 0.15),
                awal: Math.random(),
            };
        });
    };

    const titikBezier = ([[x0, y0], [x1, y1], [x2, y2], [x3, y3]], t) => {
        const s = 1 - t;
        return [
            s * s * s * x0 + 3 * s * s * t * x1 + 3 * s * t * t * x2 + t * t * t * x3,
            s * s * s * y0 + 3 * s * s * t * y1 + 3 * s * t * t * y2 + t * t * t * y3,
        ];
    };

    // Posisi awal (sebelum terbang) dan arah menyebar diacak setiap siklus.
    const acakSiklus = () => {
        titik.forEach((t) => {
            t.sx = t.sisi < 0 ? acak(-0.3, 0.25) * lebar : acak(0.75, 1.3) * lebar;
            t.sy = acak(-0.1, 1.1) * tinggi;
            t.ax = t.sisi * acak(80, 260);
            t.ay = acak(-90, 90);
        });
    };

    // ---------- Menggambar satu bingkai ----------
    const px = new Float32Array(titik.length);
    const py = new Float32Array(titik.length);
    const pa = new Float32Array(titik.length);
    const pp = new Float32Array(titik.length);

    const gambar = (waktu, diam = false) => {
        const w = diam ? 5 : waktu % SIKLUS;
        ctx.clearRect(0, 0, lebar, tinggi);

        // Serat optik dan kilau cahaya yang mengalir di atasnya.
        ctx.lineWidth = 1;
        serat.forEach((s) => {
            ctx.strokeStyle = `rgba(${s.warna}, .12)`;
            ctx.beginPath();
            ctx.moveTo(...s.titik[0]);
            ctx.bezierCurveTo(...s.titik[1], ...s.titik[2], ...s.titik[3]);
            ctx.stroke();

            if (diam) {
                return;
            }
            const kepala = (s.awal + waktu * s.laju) % 1.15;
            for (let k = 0; k < 10; k += 1) {
                const t = kepala - k * 0.008;
                if (t < 0 || t > 1) {
                    continue;
                }
                const [x, y] = titikBezier(s.titik, t);
                ctx.fillStyle = `rgba(${s.warna}, ${0.55 * (1 - k / 10)})`;
                ctx.beginPath();
                ctx.arc(x, y, 1.8 - k * 0.12, 0, Math.PI * 2);
                ctx.fill();
            }
        });

        // Goyangan saat bersalaman.
        let goyang = 0;
        if (!diam) {
            SALAMAN.forEach((s) => {
                const d = w - s;
                if (d >= 0 && d < 0.9) {
                    goyang += 7 * Math.sin(d * Math.PI * 2 * 2.2) * (1 - d / 0.9);
                }
            });
        }

        const menyebar = diam ? 0 : batas((w - MULAI_MENYEBAR) / (SIKLUS - MULAI_MENYEBAR));

        titik.forEach((t, i) => {
            const p = diam ? 1 : batas((w - t.tunda) / LAMA_TERBANG);
            const e = mudahKeluar(p);
            // Gerak "bernapas" halus yang menyatu sepanjang garis (titik bertetangga bergerak bersama),
            // sehingga garis tetap mulus seperti ikon.
            const napasX = diam ? 0 : Math.sin(waktu * 0.8 + t.ty * 0.045) * 0.45;
            const napasY = diam ? 0 : Math.cos(waktu * 0.9 + t.tx * 0.045) * 0.45;
            const gx = pusat.x + (t.tx - GENGGAMAN.x + napasX) * skala;
            const gy = pusat.y + (t.ty - GENGGAMAN.y + napasY + goyang * t.goyang) * skala;
            const sebar = menyebar * menyebar;
            px[i] = t.sx + (gx - t.sx) * e + t.ax * sebar;
            py[i] = t.sy + (gy - t.sy) * e + t.ay * sebar;
            pp[i] = p;
            // Di belakang teks (layar lebar) titik memudar agar tulisan tetap mudah dibaca.
            const diBelakangTeks = batas((px[i] - (batasTeks - 140)) / 170);
            pa[i] = p * t.pudar * (1 - menyebar) * diBelakangTeks;
        });

        // Garis tepi tangan: tersambung antar-titik berurutan, muncul setelah kedua titik mendarat.
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = Math.min(3.6, Math.max(1.8, skala * 1.5));
        sambungan.forEach(([a, b]) => {
            if (pp[a] < 0.97 || pp[b] < 0.97) {
                return;
            }
            const alfa = Math.min(pa[a], pa[b]) * (1 - batas(menyebar * 2)) * 0.85;
            if (alfa < 0.02) {
                return;
            }
            ctx.strokeStyle = `rgba(${WARNA_GARIS[titik[a].jenis]}, ${alfa})`;
            ctx.beginPath();
            ctx.moveTo(px[a], py[a]);
            ctx.lineTo(px[b], py[b]);
            ctx.stroke();
        });

        // Titik cahaya (dengan jejak gerak saat masih terbang). Di layar kecil titik diperkecil
        // agar tetap seimbang dengan garisnya.
        const ukuranTitik = Math.min(1, 0.45 + skala * 0.3);
        titik.forEach((t, i) => {
            if (pa[i] < 0.02) {
                return;
            }
            const p = pp[i];
            if (p > 0 && p < 1) {
                const e = mudahKeluar(Math.max(0, p - 0.07));
                const gx = pusat.x + (t.tx - GENGGAMAN.x) * skala;
                const gy = pusat.y + (t.ty - GENGGAMAN.y) * skala;
                ctx.strokeStyle = t.warna;
                ctx.globalAlpha = pa[i] * 0.35;
                ctx.lineWidth = t.r * 0.9;
                ctx.beginPath();
                ctx.moveTo(t.sx + (gx - t.sx) * e, t.sy + (gy - t.sy) * e);
                ctx.lineTo(px[i], py[i]);
                ctx.stroke();
            }
            if (t.halo) {
                const ukuran = t.r * 7;
                ctx.globalAlpha = pa[i] * 0.35;
                ctx.drawImage(ambilHalo(t.warna), px[i] - ukuran / 2, py[i] - ukuran / 2, ukuran, ukuran);
            }
            ctx.globalAlpha = pa[i];
            ctx.fillStyle = t.warna;
            ctx.beginPath();
            ctx.arc(px[i], py[i], t.r * ukuranTitik, 0, Math.PI * 2);
            ctx.fill();
        });
        ctx.globalAlpha = 1;

        // Gelombang cahaya dari genggaman saat bersalaman.
        if (!diam) {
            SALAMAN.forEach((s) => {
                [0, 0.18].forEach((jeda, urutan) => {
                    const d = w - s - jeda;
                    if (d < 0 || d > 1.4) {
                        return;
                    }
                    const q = d / 1.4;
                    ctx.strokeStyle = urutan === 0 ? `rgba(255, 101, 1, ${0.35 * (1 - q)})` : `rgba(0, 119, 197, ${0.3 * (1 - q)})`;
                    ctx.lineWidth = 2 * (1 - q) + 0.5;
                    ctx.beginPath();
                    ctx.arc(pusat.x, pusat.y + 10 * skala, (14 + 250 * mudahKeluar(q)) * skala, 0, Math.PI * 2);
                    ctx.stroke();
                });
            });
        }
    };

    // ---------- Jalankan hanya saat terlihat ----------
    let berjalan = false;
    let terlihat = true;
    let bingkai = 0;
    let waktu = 0;
    let sebelumnya = 0;
    let siklusKe = -1;

    const langkah = (sekarang) => {
        waktu += Math.min(0.05, (sekarang - sebelumnya) / 1000);
        sebelumnya = sekarang;
        const ke = Math.floor(waktu / SIKLUS);
        if (ke !== siklusKe) {
            siklusKe = ke;
            acakSiklus();
        }
        gambar(waktu);
        bingkai = requestAnimationFrame(langkah);
    };

    const perbarui = () => {
        const boleh = terlihat && !document.hidden && !kurangiGerak.matches;
        if (boleh && !berjalan) {
            berjalan = true;
            sebelumnya = performance.now();
            bingkai = requestAnimationFrame(langkah);
        } else if (!boleh && berjalan) {
            berjalan = false;
            cancelAnimationFrame(bingkai);
        }
        if (kurangiGerak.matches) {
            gambar(0, true);
        }
    };

    aturUkuran();
    acakSiklus();
    gambar(0, kurangiGerak.matches);

    if ('ResizeObserver' in window) {
        const pengamat = new ResizeObserver(() => {
            aturUkuran();
            acakSiklus();
            gambar(waktu, kurangiGerak.matches);
        });
        pengamat.observe(banner);
        if (teks) {
            pengamat.observe(teks);
        }
    }

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entri]) => {
            terlihat = entri.isIntersecting;
            perbarui();
        }).observe(banner);
    }

    document.addEventListener('visibilitychange', perbarui);
    kurangiGerak.addEventListener?.('change', perbarui);
    perbarui();
})();
