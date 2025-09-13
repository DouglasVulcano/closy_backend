<?php

namespace App\Http\Requests\MessageTemplate;

class UpdateMessageTemplateRequest extends MessageTemplateRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'sometimes|string|max:50',
            'content' => 'sometimes|string',
            'variables' => 'sometimes|string',
            'type' => 'sometimes|string|in:whatsapp,email,instagram',
        ];
        return array_merge_recursive(parent::rules(), $rules);
    }
}
