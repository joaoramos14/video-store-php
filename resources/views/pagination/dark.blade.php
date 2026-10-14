@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="flex flex-wrap items-center justify-between gap-4 text-xs">
        <p class="text-[#678] tabular-nums">
            {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <ul class="flex items-center gap-1">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li><span class="px-3 py-1.5 text-[#456] select-none">Anterior</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                        class="px-3 py-1.5 text-[#9ab] hover:text-white transition-colors">Anterior</a></li>
            @endif

            {{-- Páginas --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 py-1.5 text-[#678]">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span aria-current="page"
                                    class="px-3 py-1.5 rounded-[3px] bg-[#53c7ff] text-[#14181c] font-bold tabular-nums">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}"
                                    class="px-3 py-1.5 rounded-[3px] bg-[#2c3440] text-[#9ab] hover:bg-[#456] hover:text-white transition-colors tabular-nums">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Próxima --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next"
                        class="px-3 py-1.5 text-[#9ab] hover:text-white transition-colors">Próxima</a></li>
            @else
                <li><span class="px-3 py-1.5 text-[#456] select-none">Próxima</span></li>
            @endif
        </ul>
    </nav>
@endif
