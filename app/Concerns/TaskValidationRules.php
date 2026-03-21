<?php

namespace App\Concerns;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Shared validation rules, preparation logic, and custom messages for task form requests.
 *
 * Used by both {@see StoreTaskRequest} and
 * {@see UpdateTaskRequest} to keep validation DRY.
 */
trait TaskValidationRules
{
    /**
     * Prepare the data for validation.
     *
     * When the task is marked as recurring daily, the due date is cleared
     * and the status is forced to "pending" regardless of user input.
     */
    protected function prepareForValidation(): void
    {
        if ($this->boolean('is_recurring_daily')) {
            $this->merge([
                'due_date' => null,
                'status' => 'pending',
            ]);
        } else {
            $this->merge([
                'recurring_times' => null,
            ]);
        }
    }

    /**
     * Get the base validation rules shared by store and update operations.
     *
     * Subclasses may override {@see dueDateRules()} to customise due-date
     * constraints (e.g. requiring future dates on creation only).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function taskRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'string', Rule::in(Task::STATUSES)],
            'priority' => ['required', 'string', Rule::in(Task::PRIORITIES)],
            'due_date' => $this->dueDateRules(),
            'is_recurring_daily' => ['boolean'],
            'recurring_times' => [
                $this->boolean('is_recurring_daily') ? 'required' : 'nullable',
                'nullable',
                'array',
                'min:1',
            ],
            'recurring_times.*' => [
                'required',
                'date_format:H:i',
                'distinct',
            ],
            'workspace_id' => [
                'nullable',
                Rule::exists('workspaces', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }

    /**
     * Get the validation rules for the due_date field.
     *
     * Defaults to requiring a date for non-recurring tasks.
     * Override in subclasses to add additional constraints.
     *
     * @return array<mixed>
     */
    protected function dueDateRules(): array
    {
        return [
            $this->boolean('is_recurring_daily') ? 'nullable' : 'required',
            'nullable',
            'date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    protected function taskMessages(): array
    {
        return [
            'due_date.required' => 'A due date is required for non-recurring tasks.',
            'due_date.after_or_equal' => 'The due date must be today or a future date.',
            'recurring_times.required' => 'At least one time is required for recurring daily tasks.',
            'recurring_times.min' => 'At least one time is required for recurring daily tasks.',
            'recurring_times.*.date_format' => 'Each recurring time must be in HH:MM format.',
            'recurring_times.*.distinct' => 'Recurring times must be unique.',
        ];
    }
}
