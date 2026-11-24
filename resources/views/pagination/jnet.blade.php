{{-- Navigasi halaman (UIkit pagination), dipakai lewat ->links('pagination.jnet'). --}}
@if ($paginator->hasPages())
    <nav aria-label="Navigasi halaman">
        <ul class="uk-pagination uk-flex-middle paginasi">
            @if ($paginator->onFirstPage())
                <li class="uk-disabled"><span aria-disabled="true"><span uk-pagination-previous></span> Sebelumnya</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev"><span uk-pagination-previous></span> Sebelumnya</a></li>
            @endif

            <li class="paginasi__info"><span>Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span></li>

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya <span uk-pagination-next></span></a></li>
            @else
                <li class="uk-disabled"><span aria-disabled="true">Berikutnya <span uk-pagination-next></span></span></li>
            @endif
        </ul>
    </nav>
@endif
