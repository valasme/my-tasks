<x-layouts::app :title="__('Tasks')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Tasks') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage and track your tasks.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button class="cursor-pointer" aria-label="{{ __('Create New Task') }}" href="{{ route('tasks.create') }}" icon="plus" variant="primary">
                    {{ __('New Task') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300" data-test="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300" data-test="error-message">
                {{ session('error') }}
            </div>
        @endif

        {{-- Task Table --}}
        <div>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('All Tasks') }}</flux:heading>
                <div class="w-full sm:w-48">
                    <flux:select size="sm" aria-label="{{ __('Sort tasks') }}" data-test="sort-select" onchange="window.location.href=this.value">
                        <flux:select.option value="{{ route('tasks.index') }}" :selected="!$sort">
                            {{ __('Newest First') }}
                        </flux:select.option>
                        <flux:select.option value="{{ route('tasks.index', ['sort' => 'title_asc']) }}" :selected="$sort === 'title_asc'">
                            {{ __('Title A–Z') }}
                        </flux:select.option>
                        <flux:select.option value="{{ route('tasks.index', ['sort' => 'title_desc']) }}" :selected="$sort === 'title_desc'">
                            {{ __('Title Z–A') }}
                        </flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if ($tasks->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                <flux:icon name="clipboard-document-list" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No tasks yet') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Create your first task to get started.') }}</flux:subheading>
                <flux:button class="cursor-pointer" aria-label="{{ __('Create New Task') }}" href="{{ route('tasks.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Task') }}
                </flux:button>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column class="hidden md:table-cell">{{ __('Priority') }}</flux:table.column>
                    <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
                    <flux:table.column class="hidden lg:table-cell">{{ __('Schedule') }}</flux:table.column>
                    <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($tasks as $task)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">
                                <a href="{{ route('tasks.show', $task) }}" class="hover:underline" data-test="task-title" aria-label="{{ __('View Task') }}">
                                    {{ $task->title }}
                                </a>
                            </flux:table.cell>

                            <flux:table.cell class="hidden md:table-cell">
                                <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-priority">
                                    {{ $task->priorityLabel() }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell class="hidden sm:table-cell">
                                @if ($task->is_recurring_daily)
                                    <span class="inline-flex items-center rounded-md bg-zinc-200 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200" data-test="task-recurring">
                                        {{ __('Daily') }}
                                    </span>
                                @else
                                    <span class="{{ $task->statusBadgeClasses() }} inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" data-test="task-status">
                                        {{ $task->statusLabel() }}
                                    </span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="hidden lg:table-cell">
                                @if ($task->is_recurring_daily)
                                    @php
                                        $formatted = collect($task->recurring_times)->sort()->values();
                                        $visible = $formatted->take(3);
                                        $remaining = $formatted->count() - 3;
                                    @endphp
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400" data-test="task-schedule">
                                        {{ $visible->map(fn ($t) => \Carbon\Carbon::createFromFormat('H:i', $t)->format('g:i A'))->join(', ') }}@if ($remaining > 0)<span class="ml-1 text-zinc-400 dark:text-zinc-500">+{{ $remaining }} {{ __('more') }}</span>@endif
                                    </span>
                                @elseif ($task->due_date)
                                    <span class="text-sm {{ $task->due_date->isPast() && !$task->due_date->isToday() ? 'text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}" data-test="task-schedule">
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button class="cursor-pointer" href="{{ route('tasks.edit', $task) }}" size="sm" variant="ghost" icon="pencil" data-test="edit-task" aria-label="{{ __('Edit Task: :title', ['title' => $task->title]) }}">
                                        {{ __('Edit') }}
                                    </flux:button>
                                    <flux:modal.trigger :name="'delete-task-' . $task->id">
                                        <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash" data-test="delete-task-trigger" aria-label="{{ __('Delete Task: :title', ['title' => $task->title]) }}">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                        {{-- Delete Modal --}}
                        <flux:modal :name="'delete-task-' . $task->id" class="max-w-sm">
                            <div class="space-y-4">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Task') }}</flux:heading>
                                    <flux:subheading>{{ __('Are you sure you want to delete ":title"? This action cannot be undone.', ['title' => $task->title]) }}</flux:subheading>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <flux:modal.close>
                                        <flux:button class="cursor-pointer" variant="ghost" aria-label="{{ __('Cancel Delete') }}">{{ __('Cancel') }}</flux:button>
                                    </flux:modal.close>
                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}">
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
                </flux:table.rows>
            </flux:table>

            {{-- Pagination --}}
            <div class="mt-6" data-test="pagination">
                {{ $tasks->links() }}
            </div>
        @endif
        </div>
    </div>
</x-layouts::app>
