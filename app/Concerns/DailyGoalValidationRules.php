<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Shared validation rules for daily goal form requests.
 */
trait DailyGoalValidationRules
{
    /**
     * Get the base validation rules for daily goals.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function dailyGoalRules(): array
    {
        return [
            'target_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }
}
