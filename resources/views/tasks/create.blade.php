<x-layouts::app :title="__('Create Task')">
    <div class="flex w-full flex-col gap-10">
        {{-- Header --}}
        <div class="flex flex-col gap-6">
            <div>
                <flux:button class="cursor-pointer" href="{{ route('tasks.index') }}" variant="ghost" icon="arrow-left" aria-label="{{ __('Back to Tasks') }}">
                    {{ __('Back to Tasks') }}
                </flux:button>
            </div>
            <div>
                <flux:heading size="xl">{{ __('Create Task') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Add a new task to your list.') }}</flux:subheading>
            </div>
        </div>

        <flux:separator />

        {{-- Form --}}
        <form method="POST" action="{{ route('tasks.store') }}" class="w-full max-w-2xl space-y-8" x-data="{ isRecurring: {{ old('is_recurring_daily') ? 'true' : 'false' }} }">
            @csrf

            {{-- Title --}}
            <flux:input
                name="title"
                :label="__('Title')"
                :placeholder="__('Enter task title...')"
                :value="old('title')"
                required
                data-test="task-title-input"
            />

            {{-- Description --}}
            <flux:textarea
                name="description"
                :label="__('Description')"
                :placeholder="__('Describe the task (optional)...')"
                rows="4"
                data-test="task-description-input"
            >{{ old('description') }}</flux:textarea>

            {{-- Priority --}}
            <flux:select name="priority" :label="__('Priority')" data-test="task-priority-select">
                @foreach (\App\Models\Task::PRIORITIES as $priority)
                    <flux:select.option :value="$priority" :selected="old('priority', 'low') === $priority">
                        {{ ucfirst($priority) }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            {{-- Workspace --}}
            <flux:select name="workspace_id" :label="__('Workspace')" data-test="task-workspace-select">
                <flux:select.option value="" :selected="!old('workspace_id')">
                    {{ __('None') }}
                </flux:select.option>
                @foreach ($workspaces as $workspace)
                    <flux:select.option :value="$workspace->id" :selected="old('workspace_id') == $workspace->id">
                        {{ $workspace->name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            {{-- Recurring Daily Toggle --}}
            <div class="space-y-4">
                <flux:checkbox
                    name="is_recurring_daily"
                    :label="__('Recurring daily task')"
                    :description="__('Enable to set a daily recurring time instead of a due date.')"
                    value="1"
                    x-model="isRecurring"
                    data-test="task-recurring-checkbox"
                />
            </div>

            {{-- Status (hidden when recurring) --}}
            <div x-show="!isRecurring" >
                <flux:select name="status" :label="__('Status')" data-test="task-status-select" x-bind:disabled="isRecurring">
                    @foreach (\App\Models\Task::STATUSES as $status)
                        <flux:select.option :value="$status" :selected="old('status', 'pending') === $status">
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            {{-- Due Date (hidden when recurring) --}}
            <div x-show="!isRecurring" >
                <flux:input
                    name="due_date"
                    type="date"
                    :label="__('Due Date')"
                    :value="old('due_date')"
                    :min="now()->format('Y-m-d')"
                    x-bind:disabled="isRecurring"
                    data-test="task-due-date-input"
                />
            </div>

            {{-- Recurring Times (shown when recurring) --}}
            <div x-show="isRecurring">
                <flux:label>{{ __('Daily Times') }}</flux:label>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Add one or more times this task should recur each day.') }}</p>
                <div class="mt-3 space-y-2" x-data="{ times: {{ Js::from(old('recurring_times', ['09:00'])) }} }">
                    <template x-for="(time, index) in times" :key="index">
                        <div class="flex items-center gap-2">
                            <input
                                type="time"
                                x-bind:name="'recurring_times[' + index + ']'"
                                x-model="times[index]"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-400 focus:ring-zinc-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                                data-test="task-recurring-time-input"
                            />
                            <button
                                type="button"
                                x-show="times.length > 1"
                                x-on:click="times.splice(index, 1)"
                                class="cursor-pointer inline-flex items-center justify-center rounded-md border border-zinc-200 p-1.5 text-zinc-400 transition hover:border-zinc-300 hover:text-zinc-600 dark:border-zinc-600 dark:text-zinc-500 dark:hover:border-zinc-500 dark:hover:text-zinc-300"
                                aria-label="{{ __('Remove time') }}"
                                data-test="remove-time"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                            </button>
                        </div>
                    </template>
                    <button
                        type="button"
                        x-on:click="times.push('09:00')"
                        class="cursor-pointer inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                        data-test="add-time"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add another time') }}
                    </button>
                    @error('recurring_times')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('recurring_times.*')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Hidden status for recurring --}}
            <template x-if="isRecurring">
                <input type="hidden" name="status" value="pending" />
            </template>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <flux:button class="cursor-pointer" type="submit" variant="primary" data-test="submit-task">
                    {{ __('Create Task') }}
                </flux:button>
                <flux:button class="cursor-pointer" href="{{ route('tasks.index') }}" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
