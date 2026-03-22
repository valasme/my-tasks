<x-layouts::app :title="__('Weekly Reviews')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('Weekly Reviews') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Reflect on your week and plan ahead.') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3">
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.create') }}" icon="plus" variant="primary">
                    {{ __('New Review') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Reviews --}}
        @if ($reviews->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                <flux:icon name="clipboard-document-list" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                <flux:heading size="lg" class="mb-1">{{ __('No weekly reviews yet') }}</flux:heading>
                <flux:subheading class="mb-4">{{ __('Start reviewing your week to improve productivity.') }}</flux:subheading>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.create') }}" icon="plus" variant="primary" size="sm">
                    {{ __('New Review') }}
                </flux:button>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($reviews as $review)
                    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="flex items-start justify-between">
                            <div>
                                <flux:heading size="lg">{{ __('Week of :date', ['date' => $review->week_start->format('M d, Y')]) }}</flux:heading>
                                <div class="mt-3 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                                    <div>
                                        <span class="text-zinc-500">{{ __('Completed') }}</span>
                                        <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $review->tasks_completed }}</p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500">{{ __('Created') }}</span>
                                        <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $review->tasks_created }}</p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500">{{ __('Missed') }}</span>
                                        <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $review->tasks_missed }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.show', $review) }}" size="sm" variant="ghost" icon="eye">{{ __('View') }}</flux:button>
                                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.edit', $review) }}" size="sm" variant="ghost" icon="pencil">{{ __('Edit') }}</flux:button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $reviews->links() }}</div>
        @endif
    </div>
</x-layouts::app>
