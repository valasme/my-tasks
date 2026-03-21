<x-layouts::app :title="__('Edit Workspace')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('workspaces.index') }}" variant="ghost" icon="arrow-left" aria-label="{{ __('Back to Workspaces') }}">
                    {{ __('Back to Workspaces') }}
                </flux:button>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">{{ __('Edit Workspace') }}</flux:heading>
                    <flux:subheading class="mt-1">{{ __('Update the details for ":name".', ['name' => $workspace->name]) }}</flux:subheading>
                </div>
                <flux:modal.trigger name="delete-workspace">
                    <flux:button class="cursor-pointer" icon="trash" variant="ghost" size="sm" data-test="delete-workspace-trigger" aria-label="{{ __('Delete Workspace') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <flux:separator />

        {{-- Form --}}
        <form method="POST" action="{{ route('workspaces.update', $workspace) }}" class="w-full max-w-2xl space-y-8">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <flux:input
                name="name"
                :label="__('Name')"
                :placeholder="__('Enter workspace name...')"
                :value="old('name', $workspace->name)"
                required
                data-test="workspace-name-input"
            />

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary" data-test="submit-workspace">
                    {{ __('Update Workspace') }}
                </flux:button>
                <flux:button class="cursor-pointer" href="{{ route('workspaces.show', $workspace) }}" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>

    {{-- Delete Modal --}}
    <flux:modal name="delete-workspace" class="max-w-sm">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Delete Workspace') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete ":name"? Tasks assigned to this workspace will be unassigned. This action cannot be undone.', ['name' => $workspace->name]) }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="ghost" aria-label="{{ __('Cancel Delete') }}">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}">
                    @csrf
                    @method('DELETE')
                    <flux:button class="cursor-pointer" type="submit" variant="danger" data-test="confirm-delete" aria-label="{{ __('Confirm Delete') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </flux:modal>
</x-layouts::app>
