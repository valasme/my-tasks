<x-layouts::app :title="__('Create Workspace')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost" icon="arrow-left" aria-label="{{ __('Back to Workspaces') }}">
                    {{ __('Back to Workspaces') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Create Workspace') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Add a new workspace to organize your tasks.') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        {{-- Form --}}
        <form method="POST" action="{{ route('workspaces.store') }}" class="w-full max-w-2xl space-y-8">
            @csrf

            {{-- Name --}}
            <flux:input
                name="name"
                :label="__('Name')"
                :placeholder="__('Enter workspace name...')"
                :value="old('name')"
                required
                data-test="workspace-name-input"
            />

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary" data-test="submit-workspace">
                    {{ __('Create Workspace') }}
                </flux:button>
                <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
