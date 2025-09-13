<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\BasePaginationRequest;
use Illuminate\Validation\Rule;

class LeadPaginatedRequest extends BasePaginationRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if ($this->route('name')) {
            $this->merge(['name' => $this->route('name')]);
        }
        if ($this->route('status')) {
            $this->merge(['status' => $this->route('status')]);
        }
        if ($this->route('campaign_id')) {
            $this->merge(['campaign_id' => $this->route('campaign_id')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'in:new,contacted,qualified,converted,lost'],
            'campaign_id' => ['sometimes', 'integer', Rule::exists('campaigns', 'id')
                ->where('user_id', $this->user_id)],
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
