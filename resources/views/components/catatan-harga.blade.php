{{-- Ditampilkan selama periode harga belum dikonfirmasi mitra (config jnet.paket.label_periode_harga). --}}
@if (! config('jnet.paket.label_periode_harga'))
    <p class="catatan-harga">Ketentuan periode tagihan dan biaya pemasangan dikonfirmasi oleh tim JNET saat proses pengajuan.</p>
@endif
