<x-layouts::app :title="__('Inbox')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div>
            <flux:heading size="xl">{{ __('Inbox') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Capture ideas quickly. Process them later. :count processed.', ['count' => $processedCount]) }}</flux:subheading>
        </div>

        {{-- Flash Messages --}}
        @include('partials.notifications')

        {{-- Quick Capture --}}
        <form method="POST" action="{{ route('inbox.store') }}" class="flex gap-3">
            @csrf
            <div class="flex-1">
                <flux:input name="body" :placeholder="__('Capture a thought, idea, or task...')" :value="old('body')" required />
            </div>
            <flux:button class="cursor-pointer" type="submit" variant="primary" icon="plus">{{ __('Capture') }}</flux:button>
        </form>

        <flux:separator />

        {{-- Inbox Items --}}
        <div>
            <flux:heading size="lg">{{ __('Unprocessed Items') }}</flux:heading>

            @if ($items->isEmpty())
                <div class="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 py-16 dark:border-zinc-600">
                    <flux:icon name="inbox" class="mb-4 size-12 text-zinc-400 dark:text-zinc-500" aria-hidden="true" />
                    <flux:heading size="lg" class="mb-1">{{ __('Inbox is empty') }}</flux:heading>
                    <flux:subheading>{{ __('All caught up! Capture new items above.') }}</flux:subheading>
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $item->body }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $item->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('inbox.convert', $item) }}">
                                    @csrf
                                    <flux:button class="cursor-pointer" type="submit" size="sm" variant="primary" icon="arrow-right">{{ __('To Task') }}</flux:button>
                                </form>
                                <flux:modal.trigger :name="'delete-inbox-' . $item->id">
                                    <flux:button class="cursor-pointer" size="sm" variant="ghost" icon="trash">{{ __('Delete') }}</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    @endforeach
                </div>

                @foreach ($items as $item)
                    <flux:modal :name="'delete-inbox-' . $item->id" class="max-w-sm">
                        <div class="space-y-4">
                            <div>
                                <flux:heading size="lg">{{ __('Delete Item') }}</flux:heading>
                                <flux:subheading>{{ __('Are you sure? This action cannot be undone.') }}</flux:subheading>
                            </div>
                            <div class="flex justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                                </flux:modal.close>
                                <form method="POST" action="{{ route('inbox.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button class="cursor-pointer" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                </form>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach

                <div class="mt-6">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts::app>
