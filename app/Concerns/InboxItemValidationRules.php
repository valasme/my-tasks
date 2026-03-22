<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Shared validation rules for inbox item form requests.
 */
trait InboxItemValidationRules
{
    /**
     * Get the base validation rules shared by store and update operations.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function inboxItemRules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
