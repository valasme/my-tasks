<x-layouts::app :title="__('Analytics')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Analytics') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Completion ratios and daily trends.') }}</flux:subheading>
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
            <div class="sr-only">
                @php
                    $totalLast14Days = array_sum($tasksPerDay ?: []);
                    $averagePerDay = $totalLast14Days > 0 ? round($totalLast14Days / 14, 1) : 0;
                @endphp
                {{ __('In the last 14 days, you completed :total tasks, averaging :average per day.', ['total' => $totalLast14Days, 'average' => $averagePerDay]) }}
            </div>
            <div class="mt-8 flex h-48 items-end gap-1 sm:gap-2" role="img" aria-label="{{ __('Bar chart showing daily task completions for the last 14 days') }}">
                @php $maxPerDay = max($tasksPerDay ?: [1]); @endphp
                @for ($d = 13; $d >= 0; $d--)
                    @php
                        $date = now()->subDays($d);
                        $dateStr = $date->format('Y-m-d');
                        $count = $tasksPerDay[$dateStr] ?? 0;
                        $height = $maxPerDay > 0 ? ($count / $maxPerDay) * 100 : 0;
                    @endphp
                    <div class="group relative flex h-full flex-1 flex-col items-center justify-end" aria-hidden="true">
                        <span class="mb-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 {{ $count === 0 ? 'opacity-0' : '' }}">{{ $count }}</span>
                        <div class="flex w-full h-full flex-1 items-end justify-center rounded-t-md bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="w-full rounded-t-md bg-zinc-800 transition-all dark:bg-zinc-200" style="height: {{ $height }}%"></div>
                        </div>
                        <span class="mt-3 text-[10px] sm:text-xs text-zinc-500"><time datetime="{{ $date->toIso8601String() }}">{{ $date->format('M d') }}</time></span>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</x-layouts::app>
