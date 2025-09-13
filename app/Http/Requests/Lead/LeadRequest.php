<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\BaseAuthenticatedRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends BaseAuthenticatedRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['id' => $this->route('id')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'id' => ['required', 'integer', Rule::exists('leads', 'id')
                ->where('user_id', $this->user_id)]
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'id' => __('validation.custom.lead_unavailable'),
        ];
    }
}
