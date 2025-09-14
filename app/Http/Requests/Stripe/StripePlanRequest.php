<?php

namespace App\Http\Requests\Stripe;

use App\Http\Requests\BaseAuthenticatedRequest;

class StripePlanRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'plan_id' => 'required|integer|exists:plans,id',
            'return_success_url' => 'required|url',
            'return_cancel_url' => 'required|url',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
