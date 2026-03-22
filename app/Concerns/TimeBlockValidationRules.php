<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Shared validation rules for time block form requests.
 */
trait TimeBlockValidationRules
{
    /**
     * Get the base validation rules shared by store and update operations.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function timeBlockRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'date' => $this->dateRules(),
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
        ];
    }

    /**
     * Get the validation rules for the date field.
     *
     * @return array<mixed>
     */
    protected function dateRules(): array
    {
        return ['required', 'date'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    protected function timeBlockMessages(): array
    {
        return [
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
