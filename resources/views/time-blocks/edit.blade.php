<x-layouts::app :title="__('Edit Time Block')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Time Blocks') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Edit Time Block') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Update your time block.') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form method="POST" action="{{ route('time-blocks.update', $timeBlock) }}" class="w-full max-w-2xl space-y-8">
            @csrf
            @method('PUT')

            <flux:input name="title" :label="__('Title')" :value="old('title', $timeBlock->title)" required />

            <flux:select name="task_id" :label="__('Task (optional)')">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($tasks as $task)
                    <flux:select.option :value="$task->id" :selected="old('task_id', $timeBlock->task_id) == $task->id">{{ $task->title }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input name="date" type="date" :label="__('Date')" :value="old('date', $timeBlock->date)" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:input name="start_time" type="time" :label="__('Start Time')" :value="old('start_time', $timeBlock->start_time)" required />
                <flux:input name="end_time" type="time" :label="__('End Time')" :value="old('end_time', $timeBlock->end_time)" required />
            </div>

            <flux:input name="estimated_minutes" type="number" :label="__('Estimated Minutes (optional)')" :value="old('estimated_minutes', $timeBlock->estimated_minutes)" min="1" max="480" />

            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Update Time Block') }}</flux:button>
                <flux:button class="cursor-pointer" href="{{ route('time-blocks.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
