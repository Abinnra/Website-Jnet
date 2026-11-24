{{-- Garis "serat optik" dengan kilau cahaya yang mengalir pelan (hiasan; diam jika "kurangi animasi"). --}}
<svg {{ $attributes->class(['hiasan-serat']) }} viewBox="0 0 1440 360" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="serat-biru" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#00ADEF" stop-opacity="0" />
            <stop offset=".35" stop-color="#00ADEF" stop-opacity=".42" />
            <stop offset="1" stop-color="#0077C5" stop-opacity=".15" />
        </linearGradient>
        <linearGradient id="serat-oranye" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#FF6501" stop-opacity="0" />
            <stop offset=".6" stop-color="#FF6501" stop-opacity=".38" />
            <stop offset="1" stop-color="#EC3237" stop-opacity=".1" />
        </linearGradient>
    </defs>
    <g fill="none" stroke-linecap="round" vector-effect="non-scaling-stroke">
        <g class="hiasan-serat__berkas" stroke="url(#serat-biru)">
            <path d="M-20 300 C 300 300, 520 150, 860 170 S 1260 90, 1460 60" />
            <path d="M-20 312 C 310 310, 540 168, 870 184 S 1270 106, 1460 78" />
            <path d="M-20 324 C 320 320, 560 186, 880 198 S 1280 122, 1460 96" />
            <path d="M-20 336 C 330 330, 580 204, 890 212 S 1290 138, 1460 114" />
        </g>
        <g class="hiasan-serat__berkas" stroke="url(#serat-oranye)">
            <path d="M-20 40 C 380 30, 640 120, 980 110 S 1300 210, 1460 230" />
            <path d="M-20 54 C 390 44, 650 134, 990 124 S 1310 224, 1460 246" />
        </g>
        {{-- Kilau cahaya yang mengalir di sepanjang serat --}}
        <path class="hiasan-serat__kilau" pathLength="1000" stroke="#7FD8FF" d="M-20 312 C 310 310, 540 168, 870 184 S 1270 106, 1460 78" />
        <path class="hiasan-serat__kilau hiasan-serat__kilau--2" pathLength="1000" stroke="#FFB27A" d="M-20 54 C 390 44, 650 134, 990 124 S 1310 224, 1460 246" />
    </g>
</svg>
