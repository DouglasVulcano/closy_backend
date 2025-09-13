<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends CampaignRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'sometimes|string|max:50',
            'slug' => ['sometimes', 'string', 'max:50', Rule::unique('campaigns', 'slug')
                ->ignore($this->id)],
            'status' => 'sometimes|string|in:active,draft,paused,completed,cancelled',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'details' => 'sometimes|string',
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
