<x-layouts::app :title="__('Weekly Review')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Weekly Reviews') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Week of :date', ['date' => $weeklyReview->week_start->format('M d, Y')]) }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Through :date', ['date' => $weeklyReview->week_end->format('M d, Y')]) }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Tasks Completed') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ $weeklyReview->tasks_completed }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Tasks Created') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ $weeklyReview->tasks_created }}</flux:heading>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:subheading>{{ __('Tasks Missed') }}</flux:subheading>
                <flux:heading size="xl" class="mt-2">{{ $weeklyReview->tasks_missed }}</flux:heading>
            </div>
        </div>

        {{-- Summary --}}
        @if ($weeklyReview->summary)
            <div>
                <flux:heading size="lg">{{ __('Summary') }}</flux:heading>
                <p class="mt-2 text-sm text-zinc-700 whitespace-pre-line dark:text-zinc-300">{{ $weeklyReview->summary }}</p>
            </div>
        @endif

        <div class="flex items-center gap-3">
            <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.edit', $weeklyReview) }}" variant="primary" icon="pencil">{{ __('Edit') }}</flux:button>
            <flux:modal.trigger name="delete-review">
                <flux:button class="cursor-pointer" variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="delete-review" class="max-w-sm">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">{{ __('Delete Review') }}</flux:heading>
                    <flux:subheading>{{ __('Are you sure you want to delete this weekly review?') }}</flux:subheading>
                </div>
                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <form method="POST" action="{{ route('weekly-reviews.destroy', $weeklyReview) }}">
                        @csrf
                        @method('DELETE')
                        <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                    </form>
                </div>
            </div>
        </flux:modal>
    </div>
</x-layouts::app>
