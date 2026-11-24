/*
 * Animasi banner halaman Jangkauan (hiasan, tanpa video/GIF).
 *
 * Bola bumi bertitik berputar ke arah Indonesia, lalu memperbesar ke Pulau Jawa.
 * Hanya area Probolinggo yang menyala dan "terhubung" (garis jaringan dari titik
 * pusat). Garis pantai Jawa, Madura, dan Bali digambar sederhana (perkiraan),
 * bukan peta resmi. Titik pusat diambil dari config jnet.wilayah.titik_acuan.
 *
 * - Pengunjung yang memilih "kurangi animasi" langsung melihat gambar akhir (diam).
 * - Animasi berhenti saat banner tidak terlihat di layar agar hemat baterai.
 */
(() => {
    const RAD = Math.PI / 180;

    // Garis pantai sederhana [bujur, lintang]
    const JAWA = [
        [105.22, -6.75], [105.85, -6.05], [106.1, -5.92], [106.7, -6.05], [107.0, -5.95], [107.4, -6.0],
        [107.9, -6.2], [108.3, -6.25], [108.55, -6.7], [109.1, -6.85], [109.7, -6.85], [110.4, -6.95],
        [110.75, -6.55], [110.95, -6.4], [111.3, -6.6], [111.7, -6.65], [112.1, -6.88], [112.6, -6.9],
        [112.75, -7.2], [112.85, -7.55], [113.2, -7.72], [113.6, -7.7], [114.0, -7.62], [114.4, -7.75],
        [114.42, -8.2], [114.55, -8.75], [114.0, -8.6], [113.3, -8.3], [112.6, -8.45], [111.7, -8.3],
        [110.9, -8.2], [110.3, -8.05], [109.4, -7.75], [108.6, -7.8], [107.9, -7.7], [107.0, -7.4],
        [106.4, -7.0], [105.8, -6.85],
    ];
    const MADURA = [[112.7, -7.05], [113.2, -6.88], [113.8, -6.9], [114.1, -7.0], [113.9, -7.15], [113.3, -7.2], [112.75, -7.2]];
    const BALI = [[114.45, -8.1], [114.6, -8.15], [115.1, -8.07], [115.6, -8.4], [115.7, -8.45], [115.2, -8.85], [114.9, -8.5], [114.45, -8.4]];

    // Benua dan pulau lain: garis besar kasar, hanya untuk tampilan bola bumi dari jauh.
    const BENUA = [
        [[-17, 21], [-16, 12], [-8, 4.5], [5, 5.5], [9, 4], [9, -1], [13, -6], [12, -17], [15, -27], [18, -34.5], [25, -34], [32, -29], [35, -24], [40, -15], [40, -10], [39.5, -5], [43, 0], [51, 11.5], [43, 12], [37, 18], [34, 27], [32, 31], [20, 32], [10, 37], [-1, 35.5], [-6, 35.8], [-10, 31], [-13, 27.5]],
        [[35, 28], [39, 22], [43, 13], [52, 16], [59, 22.5], [56, 26], [51, 24.5], [48, 30], [39, 32], [35, 32]],
        [[44, -25], [47, -25], [50, -15], [49, -12], [44, -17]],
        [[26, 40], [36, 36], [48, 30], [57, 25.5], [62, 25], [67, 24.5], [72.5, 21], [73, 16], [77, 8], [80, 15], [80, 16], [87, 21.5], [91, 22.5], [94, 16], [98, 16], [98.5, 8], [100.5, 3], [103.5, 1.3], [104, 10.5], [106, 10], [109, 12], [109, 15], [106, 19], [108, 21.5], [111, 21], [117, 23], [121, 28], [122, 31], [119, 35], [121, 37], [122, 40], [125, 40], [129, 35], [129, 42], [135, 43.5], [141, 52], [140, 57], [150, 59], [160, 60], [163, 70], [180, 69], [180, 72], [130, 71.5], [113, 74], [100, 78], [80, 73], [70, 73], [60, 69], [45, 68], [35, 70], [25, 71], [15, 68], [5, 62], [5, 58], [10, 54], [-5, 48], [-9, 43], [-9, 37], [-5, 36], [3, 43], [12, 44], [18, 40], [23, 37]],
        [[130, 31], [132, 34], [136, 34], [140, 35], [141, 38], [142, 41], [140, 41], [139, 38], [136, 36], [132, 35.5]],
        [[140, 42], [143.5, 42.2], [145.5, 43.3], [142, 45.5], [141, 43]],
        [[120, 18.5], [122.5, 18.5], [122, 14], [124, 12.5], [120.5, 13.8], [120, 16]],
        [[122, 8], [126.5, 8.5], [126, 6.5], [125, 5.8], [122, 7]],
        [[95.3, 5.6], [97.5, 5.2], [100.5, 2], [104, -1], [106, -3], [105.9, -5.8], [104.5, -5.9], [102, -4], [100, -1], [98.5, 1.8], [96, 3.8]],
        [[109, 1.5], [109.6, -1], [110.5, -3], [114.5, -4], [116.5, -2.5], [118, 1], [119, 5], [117, 7], [115.5, 5], [113, 3], [111, 1.6]],
        [[119.5, -5.5], [120.5, -5.5], [121, -2.5], [122.5, -4.7], [123.2, -4.3], [121.5, -1.5], [123, -0.9], [125, 1.5], [124, 0.4], [120.5, 0.5], [119.8, -0.5], [119.2, -3.5]],
        [[131, -1], [134, -0.8], [138, -1.6], [141, -2.6], [146, -5], [150, -10.5], [147, -10], [143, -9], [141, -9], [138, -8.4], [137.5, -5], [133, -4], [132, -2.5]],
        [[114, -22], [114, -34], [118, -35], [124, -33.8], [129, -31.5], [135, -34.8], [138, -35.5], [140, -38], [146, -39], [150, -37.5], [153, -31], [153, -25], [146, -19], [143, -11], [141.5, -13], [141, -17], [136, -15], [137, -12], [132, -11.3], [129, -15], [126, -14], [122, -17.5], [119, -20]],
        [[116, -8.3], [119, -8.1], [123, -8.3], [122, -8.8], [116, -9]],
        [[124, -9.2], [127, -8.4], [125, -10], [123.5, -10.3]],
    ];

    // Titik simpul di sekitar pusat (selisih bujur, lintang); hiasan, bukan lokasi pelanggan.
    const SIMPUL = [[-0.42, -0.2], [0.3, -0.24], [0.55, -0.08], [-0.08, -0.42], [0.22, -0.5], [-0.45, -0.42], [0.62, -0.34]];

    const DURASI_PUTAR = 3200;
    const MULAI_ZOOM = 2600;
    const DURASI_ZOOM = 2600;
    const AKHIR_INTRO = MULAI_ZOOM + DURASI_ZOOM;

    const lerp = (a, b, t) => a + (b - a) * t;
    const jepit = (t) => Math.min(1, Math.max(0, t));
    const halus = (t) => t * t * (3 - 2 * t);
    const lunak = (t) => 1 - Math.pow(1 - t, 3);

    const diDalam = ([x, y], poli) => {
        let hasil = false;
        for (let i = 0, j = poli.length - 1; i < poli.length; j = i++) {
            const [xi, yi] = poli[i];
            const [xj, yj] = poli[j];
            if ((yi > y) !== (yj > y) && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) {
                hasil = !hasil;
            }
        }
        return hasil;
    };

    const titikDaratan = (poli, langkah) => {
        const bujur = poli.map((p) => p[0]);
        const lintang = poli.map((p) => p[1]);
        const titik = [];
        for (let x = Math.min(...bujur); x <= Math.max(...bujur); x += langkah) {
            for (let y = Math.min(...lintang); y <= Math.max(...lintang); y += langkah) {
                if (diDalam([x, y], poli)) titik.push([x, y]);
            }
        }
        return titik;
    };

    // Titik-titik permukaan bola (sebaran Fibonacci); ditandai daratan atau laut
    const semuaDaratanKasar = [...BENUA, JAWA, MADURA, BALI];
    const titikBola = (() => {
        const n = 7000;
        const emas = Math.PI * (3 - Math.sqrt(5));
        return Array.from({ length: n }, (_, i) => {
            const y = 1 - (i / (n - 1)) * 2;
            const r = Math.sqrt(1 - y * y);
            const t = emas * i;
            const titik = [Math.atan2(Math.sin(t) * r, Math.cos(t) * r) / RAD, Math.asin(y) / RAD];
            return [...titik, semuaDaratanKasar.some((poli) => diDalam(titik, poli))];
        });
    })();

    const daratan = [...titikDaratan(JAWA, 0.09), ...titikDaratan(MADURA, 0.09), ...titikDaratan(BALI, 0.09)];

    const pasang = (kanvas) => {
        const ctx = kanvas.getContext('2d');
        if (!ctx) return;

        const pusat = [Number(kanvas.dataset.bujur) || 113.2159, Number(kanvas.dataset.lintang) || -7.7543];
        const label = kanvas.dataset.label || 'Probolinggo';
        const simpul = SIMPUL.map(([dx, dy]) => [pusat[0] + dx, pusat[1] + dy]).filter((p) => diDalam(p, JAWA));
        const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

        let ukuran = 0;
        let dpr = 1;
        let mulai = null;
        let bingkai = null;
        let terlihat = true;

        const aturUkuran = () => {
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            ukuran = kanvas.clientWidth;
            kanvas.width = Math.round(ukuran * dpr);
            kanvas.height = Math.round(ukuran * dpr);
        };

        const proyeksi = (bujur, lintang, lam0, phi0, R, cx, cy) => {
            const l = (bujur - lam0) * RAD;
            const f = lintang * RAD;
            const f0 = phi0 * RAD;
            const cosc = Math.sin(f0) * Math.sin(f) + Math.cos(f0) * Math.cos(f) * Math.cos(l);
            const x = R * Math.cos(f) * Math.sin(l);
            const y = R * (Math.cos(f0) * Math.sin(f) - Math.sin(f0) * Math.cos(f) * Math.cos(l));
            return [cx + x, cy - y, cosc];
        };

        const gambar = (t) => {
            const s = ukuran;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, s, s);

            const putar = lunak(jepit(t / DURASI_PUTAR));
            const zoom = halus(jepit((t - MULAI_ZOOM) / DURASI_ZOOM));
            const akhir = jepit((t - AKHIR_INTRO + 400) / 900);

            // Sudut pandang: dari Samudra Hindia berputar ke Jawa Timur
            const lam0 = lerp(55, lerp(110.5, pusat[0] - 0.6, zoom), putar);
            const phi0 = lerp(18, -7.6, putar);
            const R0 = s * 0.4;
            const R1 = (s * 0.96) / (6.2 * RAD);
            const R = R0 * Math.pow(R1 / R0, zoom);
            const cx = s / 2;
            const cy = s / 2;

            // Atmosfer dan tepi bola
            const aBola = 1 - jepit((zoom - 0.12) / 0.33);
            if (aBola > 0) {
                const sinar = ctx.createRadialGradient(cx, cy, R * 0.85, cx, cy, R * 1.18);
                sinar.addColorStop(0, `rgba(0, 173, 239, ${0.22 * aBola})`);
                sinar.addColorStop(1, 'rgba(0, 173, 239, 0)');
                ctx.fillStyle = sinar;
                ctx.beginPath();
                ctx.arc(cx, cy, R * 1.18, 0, Math.PI * 2);
                ctx.fill();

                const isi = ctx.createRadialGradient(cx - R * 0.3, cy - R * 0.35, R * 0.1, cx, cy, R);
                isi.addColorStop(0, `rgba(0, 119, 197, ${0.35 * aBola})`);
                isi.addColorStop(1, `rgba(2, 29, 58, ${0.55 * aBola})`);
                ctx.fillStyle = isi;
                ctx.beginPath();
                ctx.arc(cx, cy, R, 0, Math.PI * 2);
                ctx.fill();

                ctx.strokeStyle = `rgba(127, 216, 255, ${0.4 * aBola})`;
                ctx.lineWidth = 1;
                ctx.stroke();

                const rBola = Math.max(0.7, Math.min(1.6, R * 0.0085));
                for (const [bj, lt, darat] of titikBola) {
                    const [x, y, z] = proyeksi(bj, lt, lam0, phi0, R, cx, cy);
                    if (z <= 0 || x < -4 || y < -4 || x > s + 4 || y > s + 4) continue;
                    ctx.fillStyle = darat
                        ? `rgba(170, 215, 255, ${(0.3 + 0.7 * z) * aBola})`
                        : `rgba(120, 170, 230, ${0.12 * z * aBola})`;
                    ctx.fillRect(x - rBola, y - rBola, rBola * 2, rBola * 2);
                }
            }

            // Pulau Jawa, Madura, Bali (titik); area Probolinggo menyala
            const aDarat = jepit((zoom - 0.2) / 0.4);
            const rTitik = Math.max(0.8, Math.min(2.4, R * 0.09 * RAD * 0.36));
            for (const [bj, lt] of aDarat > 0 ? daratan : []) {
                const [x, y, z] = proyeksi(bj, lt, lam0, phi0, R, cx, cy);
                if (z <= 0 || x < -4 || y < -4 || x > s + 4 || y > s + 4) continue;
                const jarak = Math.hypot(bj - pusat[0], (lt - pusat[1]) * 1.4);
                const nyala = akhir * jepit(1 - (jarak - 0.45) / 0.35);
                if (nyala > 0.02) {
                    ctx.fillStyle = `rgba(${Math.round(lerp(200, 40, nyala))}, ${Math.round(lerp(220, 214, nyala))}, 255, ${lerp(0.4, 1, nyala) * aDarat})`;
                } else {
                    ctx.fillStyle = `rgba(200, 220, 245, ${0.38 * aDarat})`;
                }
                ctx.beginPath();
                ctx.arc(x, y, rTitik * (1 + 0.35 * nyala), 0, Math.PI * 2);
                ctx.fill();
            }

            // Titik pusat
            const [px, py, pz] = proyeksi(pusat[0], pusat[1], lam0, phi0, R, cx, cy);
            if (pz <= 0) return;
            const aPin = jepit((zoom - 0.55) / 0.3);
            if (aPin <= 0) return;

            // Garis jaringan pusat -> simpul, dengan cahaya yang mengalir
            const fase = (t % 2400) / 2400;
            simpul.forEach(([bj, lt], i) => {
                const [nx, ny] = proyeksi(bj, lt, lam0, phi0, R, cx, cy);
                const muncul = jepit(akhir * 1.4 - i * 0.08);
                if (muncul <= 0) return;
                const ex = lerp(px, nx, muncul);
                const ey = lerp(py, ny, muncul);
                ctx.strokeStyle = `rgba(190, 240, 255, ${0.85 * aPin})`;
                ctx.lineWidth = 1.6;
                ctx.beginPath();
                ctx.moveTo(px, py);
                ctx.quadraticCurveTo((px + ex) / 2, Math.min(py, ey) - 18, ex, ey);
                ctx.stroke();

                if (muncul >= 1) {
                    const u = (fase + i * 0.17) % 1;
                    const qx = (1 - u) * (1 - u) * px + 2 * (1 - u) * u * ((px + nx) / 2) + u * u * nx;
                    const qy = (1 - u) * (1 - u) * py + 2 * (1 - u) * u * (Math.min(py, ny) - 18) + u * u * ny;
                    ctx.fillStyle = '#fff';
                    ctx.beginPath();
                    ctx.arc(qx, qy, 2.6, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#00ADEF';
                    ctx.beginPath();
                    ctx.arc(nx, ny, 4, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#fff';
                    ctx.lineWidth = 2;
                    ctx.stroke();
                }
            });

            // Gelombang sinyal dari pusat
            for (let k = 0; k < 3; k += 1) {
                const g = ((t / 2000) + k / 3) % 1;
                ctx.strokeStyle = `rgba(0, 200, 255, ${(1 - g) * 0.75 * aPin})`;
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(px, py, 6 + g * s * 0.14, 0, Math.PI * 2);
                ctx.stroke();
            }

            const cahaya = ctx.createRadialGradient(px, py, 0, px, py, 22);
            cahaya.addColorStop(0, `rgba(140, 232, 255, ${0.9 * aPin})`);
            cahaya.addColorStop(1, 'rgba(0, 173, 239, 0)');
            ctx.fillStyle = cahaya;
            ctx.beginPath();
            ctx.arc(px, py, 22, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = `rgba(255, 255, 255, ${aPin})`;
            ctx.beginPath();
            ctx.arc(px, py, 5.5, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = `rgba(0, 173, 239, ${aPin})`;
            ctx.lineWidth = 2.5;
            ctx.stroke();

            // Label
            if (akhir > 0) {
                const huruf = getComputedStyle(kanvas).fontFamily || 'sans-serif';
                ctx.font = `700 ${Math.round(Math.max(13, s * 0.036))}px ${huruf}`;
                const lebar = ctx.measureText(label).width;
                const lx = Math.min(px + 14, s - lebar - 42);
                const ly = py - 34;
                ctx.globalAlpha = akhir;
                ctx.fillStyle = 'rgba(255, 255, 255, .96)';
                ctx.beginPath();
                ctx.roundRect?.(lx - 12, ly - 17, lebar + 40, 30, 15);
                ctx.fill();
                ctx.fillStyle = '#00ADEF';
                ctx.beginPath();
                ctx.arc(lx + 1, ly - 2, 4, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#021D3A';
                ctx.textBaseline = 'middle';
                ctx.fillText(label, lx + 14, ly - 2);
                ctx.globalAlpha = 1;
            }
        };

        const langkahBingkai = (waktu) => {
            if (mulai === null) mulai = waktu;
            gambar(waktu - mulai);
            bingkai = terlihat ? requestAnimationFrame(langkahBingkai) : null;
        };

        const jalankan = () => {
            cancelAnimationFrame(bingkai);
            bingkai = null;
            if (kurangiGerak.matches) {
                gambar(AKHIR_INTRO + 1200); // gambar akhir, diam
                return;
            }
            if (terlihat) bingkai = requestAnimationFrame(langkahBingkai);
        };

        aturUkuran();
        if ('ResizeObserver' in window) {
            new ResizeObserver(() => {
                aturUkuran();
                if (kurangiGerak.matches || bingkai === null) {
                    gambar(mulai === null || kurangiGerak.matches ? AKHIR_INTRO + 1200 : performance.now() - mulai);
                }
            }).observe(kanvas);
        }
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([masuk]) => {
                terlihat = masuk.isIntersecting;
                if (terlihat && bingkai === null) jalankan();
            }).observe(kanvas);
        }
        kurangiGerak.addEventListener?.('change', jalankan);
        jalankan();
    };

    const mulaiSemua = () => document.querySelectorAll('canvas[data-peta-jangkauan]').forEach(pasang);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mulaiSemua);
    } else {
        mulaiSemua();
    }
})();
