<?php

namespace App\Http\Requests;

use App\Concerns\TimeBlockValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeBlockRequest extends FormRequest
{
    use TimeBlockValidationRules;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('time_block'));
    }

    public function rules(): array
    {
        return $this->timeBlockRules();
    }

    public function messages(): array
    {
        return $this->timeBlockMessages();
    }
}
