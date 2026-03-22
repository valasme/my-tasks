<x-layouts::app :title="__('Create Someday Item')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('someday.index') }}" variant="ghost" icon="arrow-left">
                    {{ __('Back to Someday / Maybe') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Create Someday Item') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Save an idea for later.') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        <form method="POST" action="{{ route('someday.store') }}" class="w-full max-w-2xl space-y-8">
            @csrf

            <flux:input name="title" :label="__('Title')" :placeholder="__('Enter item title...')" :value="old('title')" required />

            <flux:textarea name="description" :label="__('Description (optional)')" :placeholder="__('Add any notes...')" rows="4">{{ old('description') }}</flux:textarea>

            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary">{{ __('Create Item') }}</flux:button>
                <flux:button class="cursor-pointer" href="{{ route('someday.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
