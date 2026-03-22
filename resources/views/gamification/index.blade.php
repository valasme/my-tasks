<x-layouts::app :title="__('Gamification')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Gamification') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Track your XP, level, and daily goals.') }}</flux:subheading>
        </div>

        {{-- XP Summary --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Total XP') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ number_format($userXp?->total_xp ?? 0) }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Level') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ $userXp?->level ?? 1 }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('XP Per Level') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ \App\Models\UserXp::XP_PER_LEVEL }}</flux:heading>
            </div>
        </div>

        {{-- XP Progress Bar --}}
        @if ($userXp)
            @php
                $xpForNextLevel = ($userXp->level) * 100;
                $xpInCurrentLevel = $userXp->total_xp - (($userXp->level - 1) * 100);
                $progressPercent = min(100, ($xpInCurrentLevel / max(1, $xpForNextLevel)) * 100);
            @endphp
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-2 flex items-center justify-between">
                    <flux:subheading>{{ __('Progress to Level :level', ['level' => $userXp->level + 1]) }}</flux:subheading>
                    <span class="text-sm text-zinc-500">{{ $xpInCurrentLevel }} / {{ $xpForNextLevel }} XP</span>
                </div>
                <div class="h-3 w-full rounded-full bg-zinc-200 dark:bg-zinc-700" aria-hidden="true">
                    <div class="h-3 rounded-full bg-zinc-800 transition-all dark:bg-zinc-200" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
        @endif

        <flux:separator />

        {{-- Today's Daily Goal --}}
        <div>
            <flux:heading size="lg">{{ __("Today's Goal") }}</flux:heading>
            @if ($todayGoal)
                <div class="mt-4 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                @if ($todayGoal->is_met)
                                    <flux:icon name="check-circle" class="size-5 text-zinc-600 dark:text-zinc-300" aria-hidden="true" />
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ __('Goal Met!') }}</span>
                                @else
                                    <flux:icon name="circle-stack" class="size-5 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ __('In Progress') }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Complete :target tasks today', ['target' => $todayGoal->target_count]) }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500">
                                {{ __(':completed / :target completed', ['completed' => $todayGoal->completed_count, 'target' => $todayGoal->target_count]) }}
                            </p>
                        </div>
                        <div class="h-2 w-32 rounded-full bg-zinc-200 dark:bg-zinc-700" aria-hidden="true">
                            <div class="h-2 rounded-full bg-zinc-800 transition-all dark:bg-zinc-200" style="width: {{ min(100, ($todayGoal->completed_count / max(1, $todayGoal->target_count)) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-12 dark:border-zinc-600">
                    <flux:icon name="trophy" class="mb-4 size-10 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:subheading>{{ __('No daily goal set for today.') }}</flux:subheading>
                    <form method="POST" action="{{ route('gamification.daily-goal') }}" class="mt-4 flex items-center gap-3">
                        @csrf
                        <flux:input name="target_count" type="number" :value="3" min="1" max="50" class="w-20" />
                        <flux:button class="cursor-pointer" type="submit" icon="plus" variant="primary" size="sm">
                            {{ __('Set Goal') }}
                        </flux:button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Weekly Goals History --}}
        @if ($weeklyGoals->isNotEmpty())
            <div>
                <flux:heading size="lg">{{ __('This Week') }}</flux:heading>
                <div class="mt-4 space-y-2">
                    @foreach ($weeklyGoals as $goal)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                @if ($goal->is_met)
                                    <flux:icon name="check-circle" class="size-4 text-zinc-800 dark:text-zinc-200" aria-hidden="true" />
                                @else
                                    <flux:icon name="x-circle" class="size-4 text-zinc-400" aria-hidden="true" />
                                @endif
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $goal->date->format('D, M d') }}</span>
                            </div>
                            <span class="text-xs text-zinc-500">{{ $goal->completed_count }}/{{ $goal->target_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <flux:separator />

        {{-- Recent XP Transactions --}}
        <div>
            <flux:heading size="lg">{{ __('Recent XP Activity') }}</flux:heading>
            @if ($recentTransactions->isEmpty())
                <flux:subheading class="mt-2">{{ __('No XP earned yet. Complete tasks to earn XP!') }}</flux:subheading>
            @else
                <div class="mt-4 space-y-2">
                    @foreach ($recentTransactions as $transaction)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-4 py-3 dark:border-zinc-800">
                            <div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $transaction->reason }}</span>
                                <p class="text-xs text-zinc-400">{{ $transaction->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-bold text-zinc-800 dark:text-zinc-200">+{{ $transaction->points }} XP</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
