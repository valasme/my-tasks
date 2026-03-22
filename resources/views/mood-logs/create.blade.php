<x-layouts::app :title="__('Log Mood')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('mood-logs.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Mood Tracker') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Log Your Mood') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('How are you feeling right now?') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form method="POST" action="{{ route('mood-logs.store') }}" class="w-full max-w-2xl space-y-8">
            @csrf

            <flux:select name="mood_score" :label="__('Mood Score')" required>
                <flux:select.option value="1">{{ __('1 - Very Low') }}</flux:select.option>
                <flux:select.option value="2">{{ __('2 - Low') }}</flux:select.option>
                <flux:select.option value="3" selected>{{ __('3 - Neutral') }}</flux:select.option>
                <flux:select.option value="4">{{ __('4 - Good') }}</flux:select.option>
                <flux:select.option value="5">{{ __('5 - Great') }}</flux:select.option>
            </flux:select>

            <flux:select name="energy_level" :label="__('Energy Level')" required>
                <flux:select.option value="1">{{ __('1 - Very Low') }}</flux:select.option>
                <flux:select.option value="2">{{ __('2 - Low') }}</flux:select.option>
                <flux:select.option value="3" selected>{{ __('3 - Moderate') }}</flux:select.option>
                <flux:select.option value="4">{{ __('4 - High') }}</flux:select.option>
                <flux:select.option value="5">{{ __('5 - Very High') }}</flux:select.option>
            </flux:select>

            <flux:textarea name="notes" :label="__('Notes (optional)')" :placeholder="__('What\'s on your mind?')" rows="4">{{ old('notes') }}</flux:textarea>

            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Log Mood') }}</flux:button>
                <flux:button class="cursor-pointer" href="{{ route('mood-logs.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
