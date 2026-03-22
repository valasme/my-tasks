<x-layouts::app :title="__('Create Weekly Review')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Weekly Reviews') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Create Weekly Review') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Reflect on the past week.') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form method="POST" action="{{ route('weekly-reviews.store') }}" class="w-full max-w-2xl space-y-8">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <flux:input name="week_start" type="date" :label="__('Week Start')" :value="old('week_start', now()->startOfWeek()->format('Y-m-d'))" required />
                <flux:input name="week_end" type="date" :label="__('Week End')" :value="old('week_end', now()->endOfWeek()->format('Y-m-d'))" required />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <flux:input name="tasks_completed" type="number" :label="__('Tasks Completed')" :value="old('tasks_completed', 0)" min="0" required />
                <flux:input name="tasks_created" type="number" :label="__('Tasks Created')" :value="old('tasks_created', 0)" min="0" required />
                <flux:input name="tasks_missed" type="number" :label="__('Tasks Missed')" :value="old('tasks_missed', 0)" min="0" required />
            </div>

            <flux:textarea name="summary" :label="__('Summary (optional)')" :placeholder="__('Summarize your week...')" rows="5">{{ old('summary') }}</flux:textarea>

            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Create Review') }}</flux:button>
                <flux:button class="cursor-pointer" href="{{ route('weekly-reviews.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
