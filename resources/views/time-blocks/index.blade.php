<x-layouts::app :title="__('Time Blocks')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Time Blocks') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Plan your day with focused time blocks.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.create') }}" icon="plus" variant="primary">
                    {{ __('New Block') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Date Navigation --}}
        <div class="flex items-center gap-4">
            <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}" variant="ghost" icon="chevron-left" size="sm">
                {{ __('Previous') }}
            </flux:button>
            <flux:heading size="lg">{{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }}</flux:heading>
            <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}" variant="ghost" icon-trailing="chevron-right" size="sm">
                {{ __('Next') }}
            </flux:button>
        </div>

        {{-- Time Blocks --}}
        @if ($timeBlocks->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                <flux:icon name="calendar" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No time blocks') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Plan time blocks for this day.') }}</flux:subheading>
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Block') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($timeBlocks as $block)
                    <div class="flex items-stretch gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex flex-col items-center justify-center border-r border-zinc-200 pr-4 dark:border-zinc-700">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::createFromFormat('H:i:s', $block->start_time)->format('g:i A') }}</span>
                            <span class="text-xs text-zinc-400">{{ __('to') }}</span>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ \Carbon\Carbon::createFromFormat('H:i:s', $block->end_time)->format('g:i A') }}</span>
                        </div>
                        <div class="flex-1">
                            <flux:heading size="sm">{{ $block->title }}</flux:heading>
                            @if ($block->task)
                                <flux:subheading class="mt-1">{{ __('Task: :title', ['title' => $block->task->title]) }}</flux:subheading>
                            @endif
                            @if ($block->estimated_minutes)
                                <span class="mt-1 inline-flex items-center text-xs text-zinc-500">{{ $block->estimated_minutes }} min</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <flux:button class="cursor-pointer" href="{{ route('time-blocks.edit', $block) }}" size="sm" variant="ghost" icon="pencil">{{ __('Edit') }}</flux:button>
                            <flux:modal.trigger :name="'delete-block-' . $block->id">
                                <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash">{{ __('Delete') }}</flux:button>
                            </flux:modal.trigger>
                        </div>
                    </div>
                @endforeach
            </div>

            @foreach ($timeBlocks as $block)
                <flux:modal :name="'delete-block-' . $block->id" class="max-w-sm">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Delete Time Block') }}</flux:heading>
                            <flux:subheading>{{ __('Are you sure you want to delete ":title"?', ['title' => $block->title]) }}</flux:subheading>
                        </div>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <form method="POST" action="{{ route('time-blocks.destroy', $block) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </div>
                </flux:modal>
            @endforeach
        @endif
    </div>
</x-layouts::app>
