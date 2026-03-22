<?php

namespace App\Http\Requests;

use App\Concerns\TimeBlockValidationRules;
use App\Models\TimeBlock;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimeBlockRequest extends FormRequest
{
    use TimeBlockValidationRules;

    public function authorize(): bool
    {
        return $this->user()->can('create', TimeBlock::class);
    }

    public function rules(): array
    {
        return $this->timeBlockRules();
    }

    protected function dateRules(): array
    {
        return ['required', 'date', 'after_or_equal:today'];
    }

    public function messages(): array
    {
        return $this->timeBlockMessages();
    }
}
