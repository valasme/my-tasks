<x-layouts::app :title="__('Mood Tracker')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Mood Tracker') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Track your mood and energy throughout the day.') }}</flux:subheading>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Log Mood Form --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-4">{{ __('Log Your Mood') }}</flux:heading>
            <form method="POST" action="{{ route('mood-logs.store') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <flux:select name="mood" :label="__('How are you feeling?')" required>
                        <flux:select.option value="">{{ __('Select mood...') }}</flux:select.option>
                        @foreach (\App\Models\MoodLog::MOODS as $mood)
                            <flux:select.option value="{{ $mood }}" :selected="old('mood') === $mood">{{ ucfirst($mood) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="flex-1">
                    <flux:input name="note" :label="__('Note (optional)')" :placeholder="__('How are you feeling?')" :value="old('note')" />
                </div>
                <flux:button class="cursor-pointer" type="submit" variant="primary" icon="plus">{{ __('Log') }}</flux:button>
            </form>
        </div>

        {{-- Mood Distribution --}}
        @if (!empty($moodDistribution))
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach (\App\Models\MoodLog::MOODS as $mood)
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:subheading>{{ ucfirst($mood) }}</flux:subheading>
                        <flux:heading size="xl" class="mt-2">{{ $moodDistribution[$mood] ?? 0 }}</flux:heading>
                    </div>
                @endforeach
            </div>
        @endif

        <flux:separator />

        {{-- Mood Logs --}}
        @if ($logs->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600" role="status">
                <flux:icon name="face-smile" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No mood logs yet') }}</flux:heading>
                <flux:subheading>{{ __('Start tracking your mood to see patterns.') }}</flux:subheading>
            </div>
        @else
            <ul class="space-y-3" role="list">
                @foreach ($logs as $log)
                    <li>
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $log->moodBadgeClasses() }}" aria-label="{{ $log->moodLabel() }}">
                                    {{ $log->moodLabel() }}
                                </span>
                                <div>
                                    @if ($log->note)
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $log->note }}</p>
                                    @endif
                                    @if ($log->task)
                                        <p class="mt-0.5 text-xs text-zinc-400">{{ __('Task: :title', ['title' => $log->task->title]) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-zinc-400">
                                    <time datetime="{{ $log->logged_at->toIso8601String() }}">{{ $log->logged_at->format('M d, g:i A') }}</time>
                                </span>
                                <flux:modal.trigger :name="'delete-mood-' . $log->id">
                                    <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash" aria-label="{{ __('Delete mood log from :date', ['date' => $log->logged_at->format('M d, g:i A')]) }}" />
                                </flux:modal.trigger>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @foreach ($logs as $log)
                <flux:modal :name="'delete-mood-' . $log->id" class="max-w-sm">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Delete Mood Log') }}</flux:heading>
                            <flux:subheading>{{ __('Are you sure? This action cannot be undone.') }}</flux:subheading>
                        </div>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <form method="POST" action="{{ route('mood-logs.destroy', $log) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </div>
                </flux:modal>
            @endforeach

            <nav class="mt-4" aria-label="{{ __('Pagination') }}">{{ $logs->links() }}</nav>
        @endif
    </div>
</x-layouts::app>
