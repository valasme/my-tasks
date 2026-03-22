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

        {{-- Items --}}
        @if ($tasks->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                <flux:icon name="light-bulb" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No items yet') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Save ideas for later.') }}</flux:subheading>
                <flux:button class="cursor-pointer" href="{{ route('someday.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Item') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($tasks as $task)
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $task->title }}</p>
                            @if ($task->description)
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $task->description }}</p>
                            @endif
                            <p class="mt-1 text-xs text-zinc-400">{{ $task->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('someday.activate', $task) }}">
                                @csrf
                                <flux:button class="cursor-pointer" type="submit" size="sm" variant="primary" icon="arrow-right">{{ __('Activate') }}</flux:button>
                            </form>
                            <flux:button class="cursor-pointer" href="{{ route('tasks.edit', $task) }}" size="sm" variant="ghost" icon="pencil">{{ __('Edit') }}</flux:button>
                            <flux:modal.trigger :name="'delete-someday-' . $task->id">
                                <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash">{{ __('Delete') }}</flux:button>
                            </flux:modal.trigger>
                        </div>
                    </div>
                @endforeach
            </div>

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

            <div class="mt-4">{{ $tasks->links() }}</div>
        @endif
    </div>
</x-layouts::app>
