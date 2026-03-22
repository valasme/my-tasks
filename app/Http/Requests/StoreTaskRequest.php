<?php

namespace App\Http\Requests;

use App\Concerns\TaskValidationRules;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data when creating a new task.
 *
 * Enforces that the due date is today or in the future and delegates
 * shared validation logic to the {@see TaskValidationRules} trait.
 */
class StoreTaskRequest extends FormRequest
{
    use TaskValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
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
     * Get the validation rules for the due_date field.
     *
     * Extends the base rules to reject past dates on creation.
     *
     * @return array<mixed>
     */
    protected function dueDateRules(): array
    {
        return [
            $this->boolean('is_recurring_daily') ? 'nullable' : 'required',
            'date',
            'after_or_equal:today',
        ];
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
