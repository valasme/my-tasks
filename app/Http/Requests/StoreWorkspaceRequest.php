<?php

namespace App\Http\Requests;

use App\Concerns\WorkspaceValidationRules;
use App\Models\Workspace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data when creating a new workspace.
 *
 * Enforces that the workspace name is unique per user and delegates
 * shared validation logic to the {@see WorkspaceValidationRules} trait.
 */
class StoreWorkspaceRequest extends FormRequest
{
    use WorkspaceValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Workspace::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->workspaceRules();
    }

    /**
     * Get the validation rules for the name field.
     *
     * Extends the base rules to enforce uniqueness per user on creation.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::unique('workspaces')->where('user_id', $this->user()->id),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->workspaceMessages();
    }
}
