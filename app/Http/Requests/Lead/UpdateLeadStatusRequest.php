<?php

namespace App\Http\Requests\Lead;

class UpdateLeadStatusRequest extends LeadRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'status' => 'sometimes|string|in:new,contacted,qualified,converted,lost',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
