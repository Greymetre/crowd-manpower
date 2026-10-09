@if ($paginator->hasPages())
    <nav class="pager" aria-label="Pagination">
        <span class="pager__info">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="pager__links">
            @if ($paginator->onFirstPage())
                <span class="pager__btn is-disabled">‹</span>
            @else
                <a class="pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pager__btn is-disabled">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pager__btn is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pager__btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">›</a>
            @else
                <span class="pager__btn is-disabled">›</span>
            @endif
        </div>
    </nav>
@endif
