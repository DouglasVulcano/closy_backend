<?php

namespace App\Http\Requests\Campaign;

use App\Http\Requests\BaseAuthenticatedRequest;

class CreateCampaignRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:50',
            'slug' => 'required|string|unique:campaigns,slug',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'slug.unique' => __('validation.custom.slug_unavailable'),
        ];
    }
}
