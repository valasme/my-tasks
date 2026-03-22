<?php

namespace App\Http\Requests;

use App\Concerns\InboxItemValidationRules;
use App\Models\InboxItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreInboxItemRequest extends FormRequest
{
    use InboxItemValidationRules;

    public function authorize(): bool
    {
        return $this->user()->can('create', InboxItem::class);
    }

    public function rules(): array
    {
        return $this->inboxItemRules();
    }
}
