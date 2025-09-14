<?php

namespace App\Http\Requests\Stripe;

use App\Http\Requests\BaseAuthenticatedRequest;

class StripePortalRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'return_url' => 'required|url',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
