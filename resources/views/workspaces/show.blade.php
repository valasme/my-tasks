<x-layouts::app :title="$workspace->name">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost" icon="arrow-left" aria-label="{{ __('Back to Workspaces') }}">
                    {{ __('Back to Workspaces') }}
                </flux:button>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <flux:heading size="xl">{{ $workspace->name }}</flux:heading>
                    <flux:subheading>{{ __('Workspace details') }}</flux:subheading>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button class="cursor-pointer" href="{{ route('workspaces.edit', $workspace) }}" icon="pencil" variant="primary" size="sm" data-test="edit-workspace" aria-label="{{ __('Edit Workspace') }}">
                        {{ __('Edit') }}
                    </flux:button>
                    <flux:modal.trigger name="delete-workspace">
                        <flux:button class="cursor-pointer" icon="trash" variant="ghost" size="sm" data-test="delete-workspace-trigger" aria-label="{{ __('Delete Workspace') }}">
                            {{ __('Delete') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        <flux:separator />

        {{-- Workspace Tasks --}}
        <div>
            <flux:heading size="lg" class="mb-4">{{ __('Tasks in this Workspace') }}</flux:heading>

            @if ($tasks->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                    <flux:icon name="clipboard-document-list" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('No tasks yet') }}</flux:heading>
                    <flux:subheading>{{ __('Assign tasks to this workspace to see them here.') }}</flux:subheading>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column class="hidden md:table-cell">{{ __('Priority') }}</flux:table.column>
                        <flux:table.column class="hidden sm:table-cell">{{ __('Status') }}</flux:table.column>
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

                                <flux:table.cell class="text-right">
                                    <flux:button class="cursor-pointer" href="{{ route('tasks.edit', $task) }}" size="sm" variant="ghost" icon="pencil" data-test="edit-task" aria-label="{{ __('Edit Task: :title', ['title' => $task->title]) }}">
                                        {{ __('Edit') }}
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
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

    {{-- Delete Modal --}}
    <flux:modal name="delete-workspace" class="max-w-sm">
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
</x-layouts::app>
