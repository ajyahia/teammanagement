@if ($paginator->hasPages())
    <nav style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-top: 20px; width: 100%;">
        <div style="color: var(--text-secondary); font-size: 0.9rem;">
            {!! __('Showing') !!}
            <span style="font-weight: bold; color: var(--text-primary);">{{ $paginator->firstItem() }}</span>
            {!! __('to') !!}
            <span style="font-weight: bold; color: var(--text-primary);">{{ $paginator->lastItem() }}</span>
            {!! __('of') !!}
            <span style="font-weight: bold; color: var(--text-primary);">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </div>

        <div>
            <ul class="pagination" style="display: flex; padding-left: 0; list-style: none; margin: 0; gap: 5px;">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link" aria-hidden="true" style="opacity: 0.5; cursor: not-allowed;">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link" style="opacity: 0.5; cursor: not-allowed;">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link" aria-hidden="true" style="opacity: 0.5; cursor: not-allowed;">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
