<x-layouts::app :title="__('Workspaces')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Workspaces') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Organize your tasks into workspaces.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button class="cursor-pointer" aria-label="{{ __('Create New Workspace') }}" href="{{ route('workspaces.create') }}" icon="plus" variant="primary">
                    {{ __('New Workspace') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Filters --}}
        <form method="GET" action="{{ route('workspaces.index') }}" class="space-y-4" data-test="workspace-filters">
            {{-- Search --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        type="search"
                        :placeholder="__('Search workspaces...')"
                        :value="$filters['search'] ?? ''"
                        size="sm"
                        icon="magnifying-glass"
                        data-test="search-input"
                        aria-label="{{ __('Search workspaces') }}"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <flux:button class="cursor-pointer" type="submit" variant="primary" size="sm" data-test="apply-filters">
                        {{ __('Filter') }}
                    </flux:button>
                    @if ($filters['search'] || $filters['has_tasks'] || $filters['sort'])
                        <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost" size="sm" data-test="clear-filters">
                            {{ __('Clear') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Filter Dropdowns --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-3">
                {{-- Has Tasks --}}
                <flux:select name="has_tasks" size="sm" aria-label="{{ __('Filter by task presence') }}" data-test="filter-has-tasks">
                    <flux:select.option value="">{{ __('All Workspaces') }}</flux:select.option>
                    <flux:select.option value="with_tasks" :selected="($filters['has_tasks'] ?? '') === 'with_tasks'">
                        {{ __('With Tasks') }}
                    </flux:select.option>
                    <flux:select.option value="without_tasks" :selected="($filters['has_tasks'] ?? '') === 'without_tasks'">
                        {{ __('Without Tasks') }}
                    </flux:select.option>
                </flux:select>

                {{-- Sort --}}
                <flux:select name="sort" size="sm" aria-label="{{ __('Sort workspaces') }}" data-test="sort-select">
                    <flux:select.option value="">{{ __('Name A–Z') }}</flux:select.option>
                    <flux:select.option value="name_desc" :selected="($filters['sort'] ?? '') === 'name_desc'">{{ __('Name Z–A') }}</flux:select.option>
                    <flux:select.option value="newest" :selected="($filters['sort'] ?? '') === 'newest'">{{ __('Newest First') }}</flux:select.option>
                    <flux:select.option value="oldest" :selected="($filters['sort'] ?? '') === 'oldest'">{{ __('Oldest First') }}</flux:select.option>
                    <flux:select.option value="tasks_desc" :selected="($filters['sort'] ?? '') === 'tasks_desc'">{{ __('Most Tasks') }}</flux:select.option>
                    <flux:select.option value="tasks_asc" :selected="($filters['sort'] ?? '') === 'tasks_asc'">{{ __('Fewest Tasks') }}</flux:select.option>
                </flux:select>
            </div>
        </form>

        {{-- Workspace Table --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">
                    {{ __('All Workspaces') }}
                    <span class="ml-1 text-sm font-normal text-zinc-500 dark:text-zinc-400">({{ $workspaces->total() }})</span>
                </flux:heading>
            </div>

            @if ($workspaces->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600" role="status">
                    <flux:icon name="rectangle-group" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />

                    @if ($filters['search'] || $filters['has_tasks'])
                        <flux:heading size="lg" class="mb-1">{{ __('No matching workspaces') }}</flux:heading>
                        <flux:subheading class="mb-4">{{ __('Try adjusting your filters or search terms.') }}</flux:subheading>
                        <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost" size="sm" icon="x-mark" data-test="clear-filters-empty">
                            {{ __('Clear Filters') }}
                        </flux:button>
                    @else
                        <flux:heading size="lg" class="mb-1">{{ __('No workspaces yet') }}</flux:heading>
                        <flux:subheading class="mb-4">{{ __('Create your first workspace to get started.') }}</flux:subheading>
                        <flux:button class="cursor-pointer" aria-label="{{ __('Create New Workspace') }}" href="{{ route('workspaces.create') }}" icon="plus" variant="primary" size="sm">
                            {{ __('New Workspace') }}
                        </flux:button>
                    @endif
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell">{{ __('Tasks') }}</flux:table.column>
                        <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($workspaces as $workspace)
                            <flux:table.row>
                                <flux:table.cell class="font-medium">
                                    <a href="{{ route('workspaces.show', $workspace) }}" class="hover:underline" data-test="workspace-name" aria-label="{{ __('View Workspace: :name', ['name' => $workspace->name]) }}">
                                        {{ $workspace->name }}
                                    </a>
                                </flux:table.cell>

                                <flux:table.cell class="hidden sm:table-cell">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400" data-test="workspace-task-count">
                                        {{ $workspace->tasks_count }} {{ Str::plural('task', $workspace->tasks_count) }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:button class="cursor-pointer" href="{{ route('workspaces.edit', $workspace) }}" size="sm" variant="ghost" icon="pencil" data-test="edit-workspace" aria-label="{{ __('Edit Workspace: :name', ['name' => $workspace->name]) }}">
                                            {{ __('Edit') }}
                                        </flux:button>
                                        <flux:modal.trigger :name="'delete-workspace-' . $workspace->id">
                                            <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash" data-test="delete-workspace-trigger" aria-label="{{ __('Delete Workspace: :name', ['name' => $workspace->name]) }}">
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Delete Modals --}}
                @foreach ($workspaces as $workspace)
                    <flux:modal :name="'delete-workspace-' . $workspace->id" class="max-w-sm">
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="lg">{{ __('Delete Workspace') }}</flux:heading>
                                <flux:subheading>{{ __('Are you sure you want to delete ":name"? Tasks assigned to this workspace will be unassigned. This action cannot be undone.', ['name' => $workspace->name]) }}</flux:subheading>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button class="cursor-pointer" variant="ghost" aria-label="{{ __('Cancel Delete') }}">{{ __('Cancel') }}</flux:button>
                                </flux:modal.close>
                                <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button class="cursor-pointer" type="submit" variant="danger" data-test="confirm-delete" aria-label="{{ __('Confirm Delete') }}">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </form>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach

                {{-- Pagination --}}
                <nav class="mt-6" data-test="pagination" aria-label="{{ __('Pagination') }}">
                    {{ $workspaces->links() }}
                </nav>
            @endif
        </div>
    </div>
</x-layouts::app>
