<?php

namespace App\Http\Requests;

use App\Concerns\DailyGoalValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreDailyGoalRequest extends FormRequest
{
    use DailyGoalValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->dailyGoalRules();
    }
}
