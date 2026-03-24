<x-layouts::app :title="__('Due Tasks')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Due Tasks') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Overview of upcoming and overdue schedules.') }}</flux:subheading>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Filters --}}
        <form method="GET" action="{{ route('due-tasks.index') }}" class="space-y-4" data-test="task-filters">
            {{-- Search --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <flux:input
                        name="search"
                        type="search"
                        :placeholder="__('Search tasks...')"
                        :value="$filters['search'] ?? ''"
                        size="sm"
                        icon="magnifying-glass"
                        data-test="search-input"
                        aria-label="{{ __('Search tasks') }}"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <flux:button class="cursor-pointer" type="submit" variant="primary" size="sm" data-test="apply-filters">
                        {{ __('Filter') }}
                    </flux:button>
                    @if ($filters['search'] || $filters['status'] || $filters['priority'] || $filters['workspace'] || $filters['sort'])
                        <flux:button class="cursor-pointer" href="{{ route('due-tasks.index') }}" variant="ghost" size="sm" data-test="clear-filters">
                            {{ __('Clear') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- Filter Dropdowns --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {{-- Status --}}
                <flux:select name="status" size="sm" aria-label="{{ __('Filter by status') }}" data-test="filter-status">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    @foreach ($statuses as $status)
                        <flux:select.option :value="$status" :selected="($filters['status'] ?? '') === $status">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Priority --}}
                <flux:select name="priority" size="sm" aria-label="{{ __('Filter by priority') }}" data-test="filter-priority">
                    <flux:select.option value="">{{ __('All Priorities') }}</flux:select.option>
                    @foreach ($priorities as $priority)
                        <flux:select.option :value="$priority" :selected="($filters['priority'] ?? '') === $priority">
                            {{ ucfirst($priority) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Workspace --}}
                <flux:select name="workspace" size="sm" aria-label="{{ __('Filter by workspace') }}" data-test="filter-workspace">
                    <flux:select.option value="">{{ __('All Workspaces') }}</flux:select.option>
                    @foreach ($workspaces as $workspace)
                        <flux:select.option :value="$workspace->id" :selected="($filters['workspace'] ?? '') == $workspace->id">
                            {{ $workspace->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Sort --}}
                <flux:select name="sort" size="sm" aria-label="{{ __('Sort tasks') }}" data-test="sort-select">
                    <flux:select.option value="">{{ __('Due Date') }}</flux:select.option>
                    <flux:select.option value="title_asc" :selected="($filters['sort'] ?? '') === 'title_asc'">{{ __('Title A-Z') }}</flux:select.option>
                    <flux:select.option value="title_desc" :selected="($filters['sort'] ?? '') === 'title_desc'">{{ __('Title Z-A') }}</flux:select.option>
                </flux:select>
            </div>
        </form>

        {{-- Incomplete Schedules --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">{{ __('Incomplete') }}</flux:heading>
            </div>

            @if ($incompleteSchedules->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600" data-test="empty-incomplete" role="status">
                    <flux:icon name="check-circle" class="mb-4 size-12 text-zinc-500 dark:text-zinc-400" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('All caught up!') }}</flux:heading>
                    <flux:subheading>{{ __('No pending or overdue tasks.') }}</flux:subheading>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Task') }}</flux:table.column>
                        <flux:table.column class="hidden md:table-cell">{{ __('Priority') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell">{{ __('Due Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($incompleteSchedules as $task)
                            <flux:table.row>
                                <flux:table.cell class="font-medium">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:underline" aria-label="{{ __('View Task: :title', ['title' => $task->title]) }}" data-test="task-title">
                                        {{ $task->title }}
                                    </a>
                                </flux:table.cell>

                                <flux:table.cell class="hidden md:table-cell">
                                    <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-priority" aria-label="{{ __('Priority: :priority', ['priority' => $task->priorityLabel()]) }}">
                                        {{ $task->priorityLabel() }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell class="hidden sm:table-cell">
                                    <time class="text-sm {{ $task->isMissed() ? 'font-medium text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}" data-test="task-due-date" datetime="{{ $task->due_date->toIso8601String() }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </time>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <span class="{{ $task->scheduleStatusBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-schedule-status" aria-label="{{ __('Status: :status', ['status' => $task->scheduleStatusLabel()]) }}">
                                        {{ $task->scheduleStatusLabel() }}
                                    </span>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>

        <flux:separator />

        {{-- Completed Schedules --}}
        <div x-data="{ showCompleted: localStorage.getItem('due_tasks_show_completed') === 'true', toggle() { this.showCompleted = !this.showCompleted; localStorage.setItem('due_tasks_show_completed', this.showCompleted); } }">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('Completed') }}</flux:heading>
                @if ($completedSchedules->isNotEmpty())
                    <flux:button class="cursor-pointer" aria-controls="completed-tasks-list" x-bind:aria-expanded="showCompleted.toString()" role="button" aria-label="{{ __('Toggle completed tasks visibility') }}" size="sm" variant="ghost" x-on:click="toggle()" data-test="toggle-completed">
                        <span x-text="showCompleted ? '{{ __('Hide') }}' : '{{ __('Show') }} ({{ $completedSchedules->total() }})'"></span>
                    </flux:button>
                @endif
            </div>

            @if ($completedSchedules->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600" data-test="empty-completed" role="status">
                    <flux:icon name="clipboard-document-list" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('No completed tasks') }}</flux:heading>
                    <flux:subheading>{{ __('Completed tasks will appear here.') }}</flux:subheading>
                </div>
            @else
                <div id="completed-tasks-list" x-show="showCompleted" x-collapse>
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Task') }}</flux:table.column>
                            <flux:table.column class="hidden md:table-cell">{{ __('Priority') }}</flux:table.column>
                            <flux:table.column class="hidden sm:table-cell">{{ __('Due Date') }}</flux:table.column>
                            <flux:table.column class="hidden sm:table-cell">{{ __('Completed') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($completedSchedules as $task)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium">
                                        <a href="{{ route('tasks.show', $task) }}" class="hover:underline" aria-label="{{ __('View Completed Task: :title', ['title' => $task->title]) }}" data-test="task-title">
                                            {{ $task->title }}
                                        </a>
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden md:table-cell">
                                        <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-priority" aria-label="{{ __('Priority: :priority', ['priority' => $task->priorityLabel()]) }}">
                                            {{ $task->priorityLabel() }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden sm:table-cell">
                                        @if ($task->due_date)
                                            <time class="text-sm text-zinc-600 dark:text-zinc-400" data-test="task-due-date" datetime="{{ $task->due_date->toIso8601String() }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                            </time>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">&mdash;</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden sm:table-cell">
                                        @if ($task->completed_at)
                                            <time class="text-sm text-zinc-600 dark:text-zinc-400" data-test="task-completed-at" datetime="{{ $task->completed_at->toIso8601String() }}">
                                                {{ $task->completed_at->format('M d, Y') }}
                                            </time>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">&mdash;</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <span class="{{ $task->scheduleStatusBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-schedule-status" aria-label="{{ __('Status: :status', ['status' => $task->scheduleStatusLabel()]) }}">
                                            {{ $task->scheduleStatusLabel() }}
                                        </span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    {{-- Pagination --}}
                    <nav class="mt-6" data-test="pagination" aria-label="{{ __('Pagination') }}">
                        {{ $completedSchedules->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
