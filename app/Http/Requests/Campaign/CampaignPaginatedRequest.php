<?php

namespace App\Http\Requests\Campaign;

use App\Http\Requests\BasePaginationRequest;

class CampaignPaginatedRequest extends BasePaginationRequest
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
        if ($this->route('status')) {
            $this->merge(['status' => $this->route('status')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', 'string'],
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
