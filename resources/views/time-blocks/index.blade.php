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
        @php
            $currentDate = \Carbon\Carbon::parse($date);
            $isToday = $currentDate->isToday();
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-2">
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => $currentDate->copy()->subWeek()->format('Y-m-d')]) }}" variant="ghost" icon="chevron-double-left" size="sm" aria-label="{{ __('Previous week') }}" data-test="prev-week" />
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => $currentDate->copy()->subDay()->format('Y-m-d')]) }}" variant="ghost" icon="chevron-left" size="sm" aria-label="{{ __('Previous day') }}" data-test="prev-day" />
            </div>

            <div class="flex items-center gap-3">
                <flux:heading size="lg" data-test="current-date">{{ $currentDate->format('l, M d, Y') }}</flux:heading>
                @if ($isToday)
                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-500/30">{{ __('Today') }}</span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => $currentDate->copy()->addDay()->format('Y-m-d')]) }}" variant="ghost" icon="chevron-right" size="sm" aria-label="{{ __('Next day') }}" data-test="next-day" />
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index', ['date' => $currentDate->copy()->addWeek()->format('Y-m-d')]) }}" variant="ghost" icon="chevron-double-right" size="sm" aria-label="{{ __('Next week') }}" data-test="next-week" />
            </div>

            <div class="flex items-center gap-2 sm:ml-auto">
                @unless ($isToday)
                    <flux:button class="cursor-pointer" href="{{ route('time-blocks.index') }}" variant="subtle" size="sm" icon="arrow-uturn-left" data-test="jump-today">
                        {{ __('Today') }}
                    </flux:button>
                @endunless
                <form method="GET" action="{{ route('time-blocks.index') }}" class="flex items-center gap-2" data-test="date-picker-form">
                    <input
                        type="date"
                        name="date"
                        value="{{ $currentDate->format('Y-m-d') }}"
                        onchange="this.form.submit()"
                        class="rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        data-test="date-picker"
                        aria-label="{{ __('Jump to date') }}"
                    />
                </form>
            </div>
        </div>

        {{-- Time Blocks for Selected Date --}}
        @if ($timeBlocks->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600" data-test="empty-day">
                <flux:icon name="calendar" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No time blocks') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Plan time blocks for this day.') }}</flux:subheading>
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Block') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-3" data-test="day-blocks">
                @foreach ($timeBlocks as $block)
                    <div class="flex items-stretch gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex flex-col items-center justify-center border-r border-zinc-200 pr-4 dark:border-zinc-700">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $block->formattedStartTime() }}</span>
                            <span class="text-xs text-zinc-400">{{ __('to') }}</span>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $block->formattedEndTime() }}</span>
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

        <flux:separator />

        {{-- All Time Blocks --}}
        <div x-data="{ showAll: localStorage.getItem('time_blocks_show_all') === 'true', toggle() { this.showAll = !this.showAll; localStorage.setItem('time_blocks_show_all', this.showAll); } }">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('All Time Blocks') }}</flux:heading>
                @if ($allTimeBlocks->isNotEmpty())
                    <flux:button class="cursor-pointer" aria-controls="all-time-blocks-list" x-bind:aria-expanded="showAll.toString()" role="button" aria-label="{{ __('Toggle all time blocks visibility') }}" size="sm" variant="ghost" x-on:click="toggle()" data-test="toggle-all-blocks">
                        <span x-text="showAll ? '{{ __('Hide') }}' : '{{ __('Show') }} ({{ $allTimeBlocks->total() }})'"></span>
                    </flux:button>
                @endif
            </div>

            @if ($allTimeBlocks->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600" data-test="empty-all-blocks">
                    <flux:icon name="calendar" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('No time blocks yet') }}</flux:heading>
                    <flux:subheading>{{ __('Your time blocks will appear here.') }}</flux:subheading>
                </div>
            @else
                <div id="all-time-blocks-list" x-show="showAll" x-collapse data-test="all-blocks-list">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Title') }}</flux:table.column>
                            <flux:table.column class="hidden sm:table-cell">{{ __('Date') }}</flux:table.column>
                            <flux:table.column class="hidden md:table-cell">{{ __('Time') }}</flux:table.column>
                            <flux:table.column class="hidden lg:table-cell">{{ __('Task') }}</flux:table.column>
                            <flux:table.column>{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($allTimeBlocks as $block)
                                <flux:table.row>
                                    <flux:table.cell class="font-medium" data-test="block-title">
                                        {{ $block->title }}
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden sm:table-cell">
                                        <a href="{{ route('time-blocks.index', ['date' => $block->date->format('Y-m-d')]) }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400" data-test="block-date">
                                            {{ $block->date->format('M d, Y') }}
                                        </a>
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden md:table-cell">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $block->formattedStartTime() }} &ndash; {{ $block->formattedEndTime() }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell class="hidden lg:table-cell">
                                        @if ($block->task)
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $block->task->title }}</span>
                                        @else
                                            <span class="text-sm text-zinc-400 dark:text-zinc-500">&mdash;</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <div class="flex items-center gap-1">
                                            <flux:button class="cursor-pointer" href="{{ route('time-blocks.edit', $block) }}" size="xs" variant="ghost" icon="pencil" aria-label="{{ __('Edit :title', ['title' => $block->title]) }}" />
                                            <flux:modal.trigger :name="'delete-all-block-' . $block->id">
                                                <flux:button class="cursor-pointer" size="xs" variant="ghost" icon="trash" aria-label="{{ __('Delete :title', ['title' => $block->title]) }}" />
                                            </flux:modal.trigger>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    {{-- Pagination --}}
                    @if ($allTimeBlocks->hasPages())
                        <div class="mt-6" data-test="all-blocks-pagination">
                            {{ $allTimeBlocks->links() }}
                        </div>
                    @endif

                    @foreach ($allTimeBlocks as $block)
                        <flux:modal :name="'delete-all-block-' . $block->id" class="max-w-sm">
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
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
