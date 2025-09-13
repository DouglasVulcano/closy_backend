<?php

namespace App\Http\Requests\Lead;

use App\Repositories\CampaignRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicCreateLeadRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $campaign = (new CampaignRepository())->findBySlug($this->route('campaign_slug'));
        $this->merge(['campaign_id' => $campaign->id, 'user_id' => $campaign->user_id]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')
                ->where('user_id', $this->user_id)],
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')
                ->where('id', $this->user_id)],
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100',
            'celular' => 'required|string|max:20',
            'question_responses' => 'required|string',
        ];
    }
}
