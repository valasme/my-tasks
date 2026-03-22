<?php

namespace App\Http\Requests;

use App\Concerns\MoodLogValidationRules;
use App\Models\MoodLog;
use Illuminate\Foundation\Http\FormRequest;

class StoreMoodLogRequest extends FormRequest
{
    use MoodLogValidationRules;

    public function authorize(): bool
    {
        return $this->user()->can('create', MoodLog::class);
    }

    public function rules(): array
    {
        return $this->moodLogRules();
    }
}
