<x-layouts::app :title="__('Analytics')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Analytics') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Productivity trends, completion ratios, and habit streaks.') }}</flux:subheading>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Total Tasks') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2 text-zinc-900 dark:text-zinc-50">{{ $totalTasks }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Completed') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2 text-zinc-900 dark:text-zinc-50">{{ $completedTasks }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Completion Rate') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2 text-zinc-900 dark:text-zinc-50">{{ $completionRatio }}%</flux:heading>
            </div>
        </div>

        <flux:separator />

        {{-- Tasks Per Day (Last 14 Days) --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Daily Completions (Last 14 Days)') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Your task completion trend over the past two weeks.') }}</flux:subheading>
            <div class="mt-8 flex h-48 items-end gap-1 sm:gap-2">
                @php $maxPerDay = max($tasksPerDay ?: [1]); @endphp
                @for ($d = 13; $d >= 0; $d--)
                    @php
                        $date = now()->subDays($d)->format('Y-m-d');
                        $count = $tasksPerDay[$date] ?? 0;
                        $height = $maxPerDay > 0 ? ($count / $maxPerDay) * 100 : 0;
                    @endphp
                    <div class="group relative flex h-full flex-1 flex-col items-center justify-end">
                        <span class="mb-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 {{ $count === 0 ? 'opacity-0' : '' }}">{{ $count }}</span>
                        <div class="flex w-full h-full flex-1 items-end justify-center rounded-t-md bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="w-full rounded-t-md bg-zinc-800 transition-all dark:bg-zinc-200" style="height: {{ $height }}%"></div>
                        </div>
                        <span class="mt-3 text-[10px] sm:text-xs text-zinc-500">{{ now()->subDays($d)->format('M d') }}</span>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Productivity by Day of Week --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Tasks Completed by Day') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Which days of the week are you most productive?') }}</flux:subheading>
            <div class="mt-8 grid grid-cols-7 gap-2 sm:gap-4">
                @php
                    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    $maxDay = max($productivityByDay ?: [1]);
                @endphp
                @foreach ($dayNames as $i => $dayName)
                    @php
                        $count = $productivityByDay[$i] ?? 0;
                        $height = $maxDay > 0 ? ($count / $maxDay) * 100 : 0;
                    @endphp
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 {{ $count === 0 ? 'opacity-0' : '' }}">{{ $count }}</span>
                        <div class="relative flex h-32 w-full items-end justify-center rounded-md bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="w-full max-w-8 rounded-t-md bg-zinc-700 dark:bg-zinc-300 transition-all" style="height: {{ $height }}%"></div>
                        </div>
                        <span class="mt-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ $dayName }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Productivity by Hour --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Tasks Completed by Hour') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Your peak productivity hours.') }}</flux:subheading>
            <div class="mt-8 flex h-40 gap-1 overflow-x-auto sm:overflow-visible">
                @php $maxHour = max($productivityByHour ?: [1]); @endphp
                @for ($h = 0; $h < 24; $h++)
                    @php
                        $count = $productivityByHour[$h] ?? 0;
                        $height = $maxHour > 0 ? ($count / $maxHour) * 100 : 0;
                    @endphp
                    <div class="relative flex h-full min-w-6 flex-1 flex-col items-center justify-end">
                        <span class="mb-1 text-[10px] text-zinc-600 dark:text-zinc-400 {{ $count === 0 ? 'opacity-0' : '' }}">{{ $count }}</span>
                        <div class="flex w-full h-full flex-1 items-end justify-center rounded-sm bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="w-full mx-px rounded-t-sm bg-zinc-500 dark:bg-zinc-400 transition-all" style="height: {{ $height }}%"></div>
                        </div>
                        <span class="mt-2 text-[10px] text-zinc-500">{{ $h }}h</span>
                    </div>
                @endfor
            </div>
        </div>

        <flux:separator />

        {{-- Habit Streaks --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Habit Streaks') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Keep your daily tasks going!') }}</flux:subheading>

            @if ($habitStreaks->isEmpty())
                <div class="mt-6 flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600">
                    <flux:icon name="fire" class="mb-3 size-10 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:subheading>{{ __('No habit streaks yet. Mark daily tasks as recurring to track streaks.') }}</flux:subheading>
                </div>
            @else
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($habitStreaks as $streak)
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div>
                                <flux:heading size="sm" class="line-clamp-1">{{ $streak->task?->title ?? __('Deleted Task') }}</flux:heading>
                                <div class="mt-1 text-sm text-zinc-500">
                                    {{ __('Best: :count', ['count' => $streak->longest_streak]) }}
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="flex items-center gap-1.5 outline-none">
                                    <flux:icon name="fire" variant="mini" class="text-zinc-700 dark:text-zinc-300" aria-hidden="true" />
                                    <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-50">{{ $streak->current_streak }}</span>
                                </div>
                                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500">{{ __('Days') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
