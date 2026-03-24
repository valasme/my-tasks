<x-layouts::app :title="__('Someday / Maybe')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Someday / Maybe') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Ideas and tasks you might do in the future.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button class="cursor-pointer" href="{{ route('someday.create') }}" icon="plus" variant="primary">
                    {{ __('New Item') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Filters --}}
        <form method="GET" action="{{ route('someday.index') }}" class="space-y-4">
            {{-- Search --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        type="search"
                        :placeholder="__('Search items...')"
                        :value="$filters['search'] ?? ''"
                        size="sm"
                        icon="magnifying-glass"
                        aria-label="{{ __('Search items') }}"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <flux:button class="cursor-pointer" type="submit" variant="primary" size="sm">
                        {{ __('Filter') }}
                    </flux:button>
                    @if ($filters['search'] || $filters['priority'] || $filters['sort'])
                        <flux:button class="cursor-pointer" href="{{ route('someday.index') }}" variant="ghost" size="sm">
                            {{ __('Clear') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Filter Dropdowns --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {{-- Priority --}}
                <flux:select name="priority" size="sm" aria-label="{{ __('Filter by priority') }}">
                    <flux:select.option value="">{{ __('All Priorities') }}</flux:select.option>
                    @foreach ($priorities as $priority)
                        <flux:select.option :value="$priority" :selected="($filters['priority'] ?? '') === $priority">
                            {{ ucfirst($priority) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Sort --}}
                <flux:select name="sort" size="sm" aria-label="{{ __('Sort items') }}">
                    <flux:select.option value="">{{ __('Newest First') }}</flux:select.option>
                    <flux:select.option value="oldest" :selected="($filters['sort'] ?? '') === 'oldest'">{{ __('Oldest First') }}</flux:select.option>
                    <flux:select.option value="title_asc" :selected="($filters['sort'] ?? '') === 'title_asc'">{{ __('Title A-Z') }}</flux:select.option>
                    <flux:select.option value="title_desc" :selected="($filters['sort'] ?? '') === 'title_desc'">{{ __('Title Z-A') }}</flux:select.option>
                    <flux:select.option value="priority_desc" :selected="($filters['sort'] ?? '') === 'priority_desc'">{{ __('Priority (High-Low)') }}</flux:select.option>
                    <flux:select.option value="priority_asc" :selected="($filters['sort'] ?? '') === 'priority_asc'">{{ __('Priority (Low-High)') }}</flux:select.option>
                </flux:select>
            </div>
        </form>

        {{-- Items --}}
        @if ($tasks->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600" role="status">
                <flux:icon name="light-bulb" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No items yet') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Save ideas for later.') }}</flux:subheading>
                <flux:button class="cursor-pointer" href="{{ route('someday.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Item') }}
                </flux:button>
            </div>
        @else
            <ul class="space-y-3" role="list">
                @foreach ($tasks as $task)
                    <li>
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $task->title }}</h3>
                                @if ($task->description)
                                    <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $task->description }}</p>
                                @endif
                                <p class="mt-1 text-xs text-zinc-400">
                                    <time datetime="{{ $task->created_at->toIso8601String() }}">{{ $task->created_at->diffForHumans() }}</time>
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('someday.activate', $task) }}">
                                    @csrf
                                    <flux:button class="cursor-pointer" type="submit" size="sm" variant="primary" icon="arrow-right" aria-label="{{ __('Activate item: :title', ['title' => $task->title]) }}">{{ __('Activate') }}</flux:button>
                                </form>
                                <flux:button class="cursor-pointer" href="{{ route('tasks.edit', $task) }}" size="sm" variant="ghost" icon="pencil" aria-label="{{ __('Edit item: :title', ['title' => $task->title]) }}">{{ __('Edit') }}</flux:button>
                                <flux:modal.trigger :name="'delete-someday-' . $task->id">
                                    <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash" aria-label="{{ __('Delete item: :title', ['title' => $task->title]) }}">{{ __('Delete') }}</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @foreach ($tasks as $task)
                <flux:modal :name="'delete-someday-' . $task->id" class="max-w-sm">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Delete Item') }}</flux:heading>
                            <flux:subheading>{{ __('Are you sure? This action cannot be undone.') }}</flux:subheading>
                        </div>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </div>
                </flux:modal>
            @endforeach

            <nav class="mt-4" aria-label="{{ __('Pagination') }}">{{ $tasks->links() }}</nav>
        @endif
    </div>
</x-layouts::app>
