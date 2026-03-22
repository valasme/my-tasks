<?php

namespace App\Concerns;

use App\Models\MoodLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Shared validation rules for mood log form requests.
 */
trait MoodLogValidationRules
{
    /**
     * Get the base validation rules for mood logs.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function moodLogRules(): array
    {
        return [
            'mood' => ['required', 'string', Rule::in(MoodLog::MOODS)],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
