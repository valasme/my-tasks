<?php

namespace App\Http\Requests;

use App\Concerns\WorkspaceValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data when updating an existing workspace.
 *
 * Enforces unique name per user while ignoring the current workspace,
 * and delegates shared validation logic to the {@see WorkspaceValidationRules} trait.
 */
class UpdateWorkspaceRequest extends FormRequest
{
    use WorkspaceValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workspace'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->workspaceRules();
    }

    /**
     * Get the validation rules for the name field.
     *
     * Extends the base rules to enforce uniqueness per user, ignoring the current workspace.
     *
     * @return array<mixed>
     */
    protected function nameRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            Rule::unique('workspaces')->where('user_id', $this->user()->id)->ignore($this->route('workspace')),
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
