<?php

namespace App\Http\Requests\MessageTemplate;

use App\Http\Requests\BaseAuthenticatedRequest;

class CreateMessageTemplateRequest extends BaseAuthenticatedRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:50',
            'content' => 'required|string',
            'variables' => 'sometimes|string',
            'type' => 'required|string|in:whatsapp,email,instagram',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
