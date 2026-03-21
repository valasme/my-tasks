<?php

namespace App\Concerns;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Shared validation rules and custom messages for workspace form requests.
 *
 * Used by both {@see StoreWorkspaceRequest} and
 * {@see UpdateWorkspaceRequest} to keep validation DRY.
 */
trait WorkspaceValidationRules
{
    /**
     * Get the base validation rules shared by store and update operations.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function workspaceRules(): array
    {
        return [
            'name' => $this->nameRules(),
        ];
    }

    /**
     * Get the validation rules for the name field.
     *
     * Defaults to requiring a non-empty string.
     * Override in subclasses to add additional constraints.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    protected function workspaceMessages(): array
    {
        return [
            'name.required' => 'A workspace name is required.',
            'name.unique' => 'You already have a workspace with this name.',
        ];
    }
}
