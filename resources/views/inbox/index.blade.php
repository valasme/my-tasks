<x-layouts::app :title="__('Inbox')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Inbox') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Capture ideas quickly. Process them later.') }}</flux:subheading>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Quick Capture --}}
        <form method="POST" action="{{ route('inbox.store') }}" class="flex gap-3">
            @csrf
            <div class="flex-1">
                <flux:input name="body" :placeholder="__('Capture a thought, idea, or task...')" :value="old('body')" required />
            </div>
            <flux:button class="cursor-pointer" type="submit" variant="primary" icon="plus">{{ __('Capture') }}</flux:button>
        </form>

        <flux:separator />

        {{-- Filters --}}
        <form method="GET" action="{{ route('inbox.index') }}" class="space-y-4">
            {{-- Status Tabs --}}
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex items-center gap-1 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                    <a href="{{ route('inbox.index', array_merge(request()->query(), ['status' => 'all', 'page' => null])) }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $filters['status'] === 'all' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        {{ __('All') }}
                        <span class="ml-1 text-xs opacity-70">({{ $counts['unprocessed'] + $counts['processed'] }})</span>
                    </a>
                    <a href="{{ route('inbox.index', array_merge(request()->query(), ['status' => 'unprocessed', 'page' => null])) }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $filters['status'] === 'unprocessed' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        {{ __('Unprocessed') }}
                        <span class="ml-1 text-xs opacity-70">({{ $counts['unprocessed'] }})</span>
                    </a>
                    <a href="{{ route('inbox.index', array_merge(request()->query(), ['status' => 'processed', 'page' => null])) }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md transition-colors {{ $filters['status'] === 'processed' ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                        {{ __('Processed') }}
                        <span class="ml-1 text-xs opacity-70">({{ $counts['processed'] }})</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if ($filters['search'] || $filters['workspace'] || $filters['sort'])
                        <flux:button class="cursor-pointer" href="{{ route('inbox.index', ['status' => $filters['status']]) }}" variant="ghost" size="sm">
                            {{ __('Clear') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Search and Dropdowns --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        type="search"
                        :placeholder="__('Search inbox...')"
                        :value="$filters['search'] ?? ''"
                        size="sm"
                        icon="magnifying-glass"
                        aria-label="{{ __('Search inbox') }}"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <flux:button type="submit" variant="primary" size="sm">
                        {{ __('Filter') }}
                    </flux:button>
                </div>
            </div>

            {{-- Filter Dropdowns --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {{-- Workspace --}}
                <flux:select name="workspace" size="sm" aria-label="{{ __('Filter by workspace') }}">
                    <flux:select.option value="">{{ __('All Workspaces') }}</flux:select.option>
                    @foreach ($workspaces as $workspace)
                        <flux:select.option :value="$workspace->id" :selected="($filters['workspace'] ?? '') == $workspace->id">
                            {{ $workspace->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Sort --}}
                <flux:select name="sort" size="sm" aria-label="{{ __('Sort items') }}">
                    <flux:select.option value="">{{ __('Newest First') }}</flux:select.option>
                    <flux:select.option value="oldest" :selected="($filters['sort'] ?? '') === 'oldest'">{{ __('Oldest First') }}</flux:select.option>
                    <flux:select.option value="title_asc" :selected="($filters['sort'] ?? '') === 'title_asc'">{{ __('Title A-Z') }}</flux:select.option>
                    <flux:select.option value="title_desc" :selected="($filters['sort'] ?? '') === 'title_desc'">{{ __('Title Z-A') }}</flux:select.option>
                </flux:select>
            </div>
        </form>

        {{-- Inbox Items --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">
                    @switch($filters['status'])
                        @case('all')
                            {{ __('All Items') }}
                            @break
                        @case('processed')
                            {{ __('Processed Items') }}
                            @break
                        @default
                            {{ __('Unprocessed Items') }}
                    @endswitch
                    <span class="ml-1 text-sm font-normal text-zinc-500 dark:text-zinc-400">({{ $items->total() }})</span>
                </flux:heading>
            </div>

            @if ($items->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                    <flux:icon name="inbox" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />

                    @if ($filters['search'] || $filters['workspace'] || $filters['sort'])
                        <flux:heading size="lg" class="mb-1">{{ __('No matching items') }}</flux:heading>
                        <flux:subheading class="mb-4">{{ __('Try adjusting your filters or search terms.') }}</flux:subheading>
                        <flux:button class="cursor-pointer" href="{{ route('inbox.index', ['status' => $filters['status']]) }}" variant="ghost" size="sm" icon="x-mark">
                            {{ __('Clear Filters') }}
                        </flux:button>
                    @else
                        <flux:heading size="lg" class="mb-1">{{ __('Inbox is empty') }}</flux:heading>
                        <flux:subheading>{{ __('All caught up! Capture new items above.') }}</flux:subheading>
                    @endif
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $item->body }}</p>
                                <p class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                    {{ $item->created_at->diffForHumans() }}
                                    @if ($item->workspace)
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
                                            {{ $item->workspace->name }}
                                        </span>
                                    @endif
                                    @if ($item->is_processed && $item->task)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                                            <flux:icon name="check" class="size-3" />
                                            {{ __('Converted to task') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if (!$item->is_processed)
                                    <form method="POST" action="{{ route('inbox.convert', $item) }}">
                                        @csrf
                                        <flux:button class="cursor-pointer" type="submit" size="sm" variant="primary" icon="arrow-right">{{ __('To Task') }}</flux:button>
                                    </form>
                                @endif
                                <flux:modal.trigger :name="'delete-inbox-' . $item->id">
                                    <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash">{{ __('Delete') }}</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    @endforeach
                </div>

                @foreach ($items as $item)
                    <flux:modal :name="'delete-inbox-' . $item->id" class="max-w-sm">
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="lg">{{ __('Delete Item') }}</flux:heading>
                                <flux:subheading>{{ __('Are you sure? This action cannot be undone.') }}</flux:subheading>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                                </flux:modal.close>
                                <form method="POST" action="{{ route('inbox.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                </form>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach

                <div class="mt-6">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
