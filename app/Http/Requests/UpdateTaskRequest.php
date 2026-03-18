<?php

namespace App\Http\Requests;

use App\Concerns\TaskValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data when updating an existing task.
 *
 * Unlike {@see StoreTaskRequest}, past due dates are permitted so that
 * existing overdue tasks can still be edited without forcing a date change.
 */
class UpdateTaskRequest extends FormRequest
{
    use TaskValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->taskRules();
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->taskMessages();
    }
}
