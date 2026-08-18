@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">

        <div class="flex gap-2 items-center justify-between w-full sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-400 bg-white border border-slate-200 cursor-not-allowed rounded-full">
                    <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('pagination.previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-full hover:border-brand hover:text-brand transition">
                    <x-icon name="arrow-left" class="w-4 h-4" /> {{ __('pagination.previous') }}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-full hover:border-brand hover:text-brand transition">
                    {{ __('pagination.next') }} <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-400 bg-white border border-slate-200 cursor-not-allowed rounded-full">
                    {{ __('pagination.next') }} <x-icon name="arrow-right" class="w-4 h-4" />
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">
                {{ __('Showing') }}
                @if ($paginator->firstItem())
                    <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
                    {{ __('to') }}
                    <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {{ __('of') }}
                <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
                {{ __('results') }}
            </p>

            <div class="flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-300 cursor-not-allowed" aria-hidden="true">
                        <x-icon name="arrow-left" class="w-4 h-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-500 bg-white border border-slate-200 hover:border-brand hover:text-brand transition" aria-label="{{ __('pagination.previous') }}">
                        <x-icon name="arrow-left" class="w-4 h-4" />
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-slate-400">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold text-white bg-gradient-to-br bg-brand hover:bg-brand-700 ">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:border-brand hover:text-brand transition" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-500 bg-white border border-slate-200 hover:border-brand hover:text-brand transition" aria-label="{{ __('pagination.next') }}">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-300 cursor-not-allowed" aria-hidden="true">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
