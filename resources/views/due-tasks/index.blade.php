<x-layouts::app :title="__('Due Tasks')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Due Tasks') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Overview of upcoming and overdue schedules.') }}</flux:subheading>
            </div>
        </div>

        {{-- Incomplete Schedules --}}
        <div>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('Incomplete') }}</flux:heading>
                <div class="w-full sm:w-48">
                    <flux:select size="sm" aria-label="{{ __('Sort incomplete tasks') }}" data-test="sort-select" onchange="window.location.href=this.value">
                        <flux:select.option value="{{ route('due-tasks.index') }}" :selected="!$sort">
                            {{ __('Due Date') }}
                        </flux:select.option>
                        <flux:select.option value="{{ route('due-tasks.index', ['sort' => 'title_asc']) }}" :selected="$sort === 'title_asc'">
                            {{ __('Title A–Z') }}
                        </flux:select.option>
                        <flux:select.option value="{{ route('due-tasks.index', ['sort' => 'title_desc']) }}" :selected="$sort === 'title_desc'">
                            {{ __('Title Z–A') }}
                        </flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if ($incompleteSchedules->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600" data-test="empty-incomplete">
                    <flux:icon name="check-circle" class="mb-4 size-12 text-green-400 dark:text-green-500" aria-hidden="true" />
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
                                    <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-priority">
                                        {{ $task->priorityLabel() }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell class="hidden sm:table-cell">
                                    <span class="text-sm {{ $task->isMissed() ? 'font-medium text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}" data-test="task-due-date">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <span class="{{ $task->scheduleStatusBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-schedule-status">
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
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600" data-test="empty-completed">
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
                                        <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-priority">
                                            {{ $task->priorityLabel() }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden sm:table-cell">
                                        @if ($task->due_date)
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400" data-test="task-due-date">
                                                {{ $task->due_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden sm:table-cell">
                                        @if ($task->completed_at)
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400" data-test="task-completed-at">
                                                {{ $task->completed_at->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <span class="{{ $task->scheduleStatusBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-schedule-status">
                                            {{ $task->scheduleStatusLabel() }}
                                        </span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    {{-- Pagination --}}
                    <div class="mt-6" data-test="pagination">
                        {{ $completedSchedules->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
