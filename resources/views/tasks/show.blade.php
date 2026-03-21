<x-layouts::app :title="$task->title">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('tasks.index') }}" variant="ghost" icon="arrow-left" aria-label="{{ __('Back to Tasks') }}">
                    {{ __('Back to Tasks') }}
                </flux:button>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="xl">{{ $task->title }}</flux:heading>
                    <flux:subheading>{{ __('Task details') }}</flux:subheading>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button class="cursor-pointer" href="{{ route('tasks.edit', $task) }}" icon="pencil" variant="primary" size="sm" data-test="edit-task" aria-label="{{ __('Edit Task') }}">
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:modal.trigger name="delete-task">
                        <flux:button class="cursor-pointer" icon="trash" variant="ghost" size="sm" data-test="delete-task-trigger" aria-label="{{ __('Delete Task') }}">
                            {{ __('Delete') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
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

        <flux:separator />

        {{-- Task Details --}}
        <div class="grid w-full max-w-2xl gap-10">
            {{-- Priority & Status --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-8">
                <div>
                    <flux:subheading class="mb-2">{{ __('Priority') }}</flux:subheading>
                    <span class="{{ $task->priorityBadgeClasses() }} inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" data-test="task-priority">
                        {{ $task->priorityLabel() }}
                    </span>
                </div>
                <div>
                    <flux:subheading class="mb-2">{{ __('Status') }}</flux:subheading>
                    @if ($task->is_recurring_daily)
                        <span class="inline-flex items-center rounded-md bg-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200" data-test="task-status">
                            {{ __('Daily Recurring') }}
                        </span>
                    @else
                        <span class="{{ $task->statusBadgeClasses() }} inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" data-test="task-status">
                            {{ $task->statusLabel() }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Schedule --}}
            <div>
                <flux:subheading class="mb-2">{{ __('Schedule') }}</flux:subheading>
                @if ($task->is_recurring_daily)
                    <div class="flex flex-wrap gap-2" data-test="task-schedule">
                        @foreach (collect($task->recurring_times)->sort()->values() as $time)
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-sm text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                {{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                            </span>
                        @endforeach
                    </div>
                @elseif ($task->due_date)
                    <p class="text-sm {{ $task->due_date->isPast() && !$task->due_date->isToday() ? 'text-red-600 dark:text-red-400' : 'text-zinc-700 dark:text-zinc-300' }}" data-test="task-schedule">
                        {{ $task->due_date->format('l, F j, Y') }}
                        @if ($task->due_date->isToday())
                            <span class="ml-1 text-xs">({{ __('Due today') }})</span>
                        @elseif ($task->due_date->isPast())
                            <span class="ml-1 text-xs">({{ __('Overdue') }})</span>
                        @endif
                    </p>
                @else
                    <p class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('No due date set') }}</p>
                @endif
            </div>

            {{-- Description --}}
            <div>
                <flux:subheading class="mb-2">{{ __('Description') }}</flux:subheading>
                @if ($task->description)
                    <p class="whitespace-pre-wrap text-sm text-zinc-700 dark:text-zinc-300" data-test="task-description">{{ $task->description }}</p>
                @else
                    <p class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('No description provided.') }}</p>
                @endif
            </div>

            {{-- Workspace --}}
            <div>
                <flux:subheading class="mb-2">{{ __('Workspace') }}</flux:subheading>
                @if ($task->workspace)
                    <a href="{{ route('workspaces.show', $task->workspace) }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-sm text-zinc-700 hover:underline dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300" data-test="task-workspace">
                        {{ $task->workspace->name }}
                    </a>
                @else
                    <p class="text-sm text-zinc-400 dark:text-zinc-500" data-test="task-workspace">{{ __('None') }}</p>
                @endif
            </div>

            {{-- Timestamps --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-8">
                <div>
                    <flux:subheading class="mb-2">{{ __('Created') }}</flux:subheading>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $task->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                <div>
                    <flux:subheading class="mb-2">{{ __('Last Updated') }}</flux:subheading>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $task->updated_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <flux:modal name="delete-task" class="max-w-sm">
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
</x-layouts::app>
