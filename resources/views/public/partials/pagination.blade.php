@if($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="disabled" aria-disabled="true">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $pageNumber => $url)
            @if($pageNumber === $paginator->currentPage())
                <span class="active" aria-current="page">{{ $pageNumber }}</span>
            @else
                <a href="{{ $url }}">{{ $pageNumber }}</a>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="disabled" aria-disabled="true">Next</span>
        @endif
    </nav>
@endif
