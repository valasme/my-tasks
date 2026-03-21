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

        {{-- Workspace Table --}}
        <div>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('All Workspaces') }}</flux:heading>
                <div class="w-full sm:w-48">
                    <flux:select size="sm" aria-label="{{ __('Sort workspaces') }}" data-test="sort-select" onchange="window.location.href=this.value">
                        <flux:select.option value="{{ route('workspaces.index') }}" :selected="!$sort">
                            {{ __('Name A–Z') }}
                        </flux:select.option>
                        <flux:select.option value="{{ route('workspaces.index', ['sort' => 'name_desc']) }}" :selected="$sort === 'name_desc'">
                            {{ __('Name Z–A') }}
                        </flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if ($workspaces->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                    <flux:icon name="rectangle-group" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('No workspaces yet') }}</flux:heading>
                    <flux:subheading class="mb-4">{{ __('Create your first workspace to get started.') }}</flux:subheading>
                    <flux:button class="cursor-pointer" aria-label="{{ __('Create New Workspace') }}" href="{{ route('workspaces.create') }}" icon="plus" variant="primary" size="sm">
                        {{ __('New Workspace') }}
                    </flux:button>
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
                                    <a href="{{ route('workspaces.show', $workspace) }}" class="hover:underline" data-test="workspace-name" aria-label="{{ __('View Workspace') }}">
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
                <div class="mt-6" data-test="pagination">
                    {{ $workspaces->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
