{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($halaman as $item)
    <url>
        <loc>{{ route($item['route']) }}</loc>
@if ($item['diubah'])
        <lastmod>{{ \Illuminate\Support\Carbon::parse($item['diubah'])->toAtomString() }}</lastmod>
@endif
        <changefreq>{{ $item['frekuensi'] }}</changefreq>
        <priority>{{ $item['prioritas'] }}</priority>
    </url>
@endforeach
</urlset>
