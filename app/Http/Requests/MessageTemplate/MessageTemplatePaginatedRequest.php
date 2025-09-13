<?php

namespace App\Http\Requests\MessageTemplate;

use App\Http\Requests\BasePaginationRequest;

class MessageTemplatePaginatedRequest extends BasePaginationRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if ($this->route('title')) {
            $this->merge(['title' => $this->route('title')]);
        }
        if ($this->route('type')) {
            $this->merge(['type' => $this->route('type')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['sometimes', 'string', 'max:50'],
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
