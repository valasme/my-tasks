@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">

        @if ($paginator->onFirstPage())
            <span class="inline-flex cursor-not-allowed items-center px-4 py-2 text-sm font-medium leading-5 text-zinc-400 dark:text-zinc-500">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex cursor-pointer items-center px-4 py-2 text-sm font-medium leading-5 text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex cursor-pointer items-center px-4 py-2 text-sm font-medium leading-5 text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex cursor-not-allowed items-center px-4 py-2 text-sm font-medium leading-5 text-zinc-400 dark:text-zinc-500">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
