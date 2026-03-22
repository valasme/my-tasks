<x-layouts::app :title="__('Edit Weekly Review')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.show', $weeklyReview) }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Review') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Edit Weekly Review') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Week of :date', ['date' => $weeklyReview->week_start->format('M d, Y')]) }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form method="POST" action="{{ route('weekly-reviews.update', $weeklyReview) }}" class="w-full max-w-2xl space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <flux:input name="week_start" type="date" :label="__('Week Start')" :value="old('week_start', $weeklyReview->week_start->format('Y-m-d'))" required />
                <flux:input name="week_end" type="date" :label="__('Week End')" :value="old('week_end', $weeklyReview->week_end->format('Y-m-d'))" required />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <flux:input name="tasks_completed" type="number" :label="__('Tasks Completed')" :value="old('tasks_completed', $weeklyReview->tasks_completed)" min="0" required />
                <flux:input name="tasks_created" type="number" :label="__('Tasks Created')" :value="old('tasks_created', $weeklyReview->tasks_created)" min="0" required />
                <flux:input name="tasks_missed" type="number" :label="__('Tasks Missed')" :value="old('tasks_missed', $weeklyReview->tasks_missed)" min="0" required />
            </div>

            <flux:textarea name="summary" :label="__('Summary (optional)')" rows="5">{{ old('summary', $weeklyReview->summary) }}</flux:textarea>

            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Update Review') }}</flux:button>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.show', $weeklyReview) }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
